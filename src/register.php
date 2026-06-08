<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function handleRegister(array $postData, array $config, array &$eventInfo): int {
    /* re-read data with exclusive lock for persistence */
    $fp = fopen($config["shiftFile"], "r+");
    if( ! flock($fp, LOCK_EX) ) {
        die(i18n("error.file_lock"));
    }
    $rawData = fread($fp, filesize($config["shiftFile"]));
    $eventInfo = json_decode($rawData, true);

    /* distinguish between pure zxnick and full mail address */
    $possibleZxNick = htmlspecialchars($_POST["data-zxnick"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $entryMail = str_contains($possibleZxNick, "@") ? $possibleZxNick : ($possibleZxNick . "@student.uni-tuebingen.de");

    /* store user data */
    $entry = array(
        "entryName" => htmlspecialchars($_POST["data-name"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        "entryMail" => $entryMail,
        "entryTimestamp" => time(),
        "entryHash" => hash("sha256", $config["hashSalt"] . time() . $_POST["data-name"] . $_POST["data-zxnick"])
    );
    $taskName = $_POST["data-task"];
    $shiftName = $_POST["data-shift"];

    /* find correct task and shift */
    $tasks = $eventInfo["eventTasks"];
    $taskIndex = array_search($taskName, array_map(fn($t) => html_entity_decode($t['i18nKey']), $tasks), true);
    $task = $tasks[$taskIndex] ?? null;
    $shifts = $tasks[$taskIndex]['taskShifts'];
    $shiftIndex = array_search($shiftName, array_map(fn($s) => html_entity_decode($s['i18nKey']), $shifts), true);
    $shift = $shifts[$shiftIndex] ?? null;
    /* prevent invalid shifts */
    if($taskIndex === FALSE || $shiftIndex === FALSE) {
        flock($fp, LOCK_UN);
        fclose($fp);
        return MSG_REGISTER_UNKNOWN;
    }

    /* error prevention */
    if( ! isset($eventInfo["eventTasks"][$taskIndex]["taskShifts"][$shiftIndex]["entries"])) {
        $eventInfo["eventTasks"][$taskIndex]["taskShifts"][$shiftIndex]["entries"] = [];
    }

    /* check if there is space left in this shift */
    if(count($eventInfo["eventTasks"][$taskIndex]["taskShifts"][$shiftIndex]["entries"]) 
        < $eventInfo["eventTasks"][$taskIndex]["taskShifts"][$shiftIndex]["shiftSlots"]) {
        /* generate feedback mail */
        $mail = new PHPMailer(true);
        try {
            /* smtp connection settings */
            $mail->isSMTP();
            $mail->Host = $config["mail"]["smtpserv"];
            $mail->SMTPAuth = true; 
            $mail->Username = $config["mail"]["username"];
            $mail->Password = $config["mail"]["password"];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            /* smtp mail settings */
            $mail->setFrom($config["mail"]["fromaddress"], $config["mail"]["fromname"]);
            $mail->addAddress($entry["entryMail"], $entry["entryName"]);
            $mail->addReplyTo($config["mail"]["replyToAddress"], $config["mail"]["replyToName"]);
            $mail->addReplyTo($config["adminMail1"], $config["adminName1"]);
            if (!empty($config["adminMail2"]) && filter_var($config["adminMail2"], FILTER_VALIDATE_EMAIL)) {
                $mail->addReplyTo($config["adminMail2"], $config["adminName2"]);}
            if (!empty($config["adminMail3"]) && filter_var($config["adminMail3"], FILTER_VALIDATE_EMAIL)) {
                $mail->addReplyTo($config["adminMail3"], $config["adminName3"]);}
            $mail->CharSet = "UTF-8";
            /* content */
            $mail->isHTML(false);
            $mail->Subject = i18n("mail.register.subject", [
                "eventName" => i18n($eventInfo["eventI18nKey"] . "_name")
            ]);
            $mail->Body = i18n("mail.register.body", [
                "entryName" => $entry["entryName"],
                "eventName" => i18n($eventInfo["eventI18nKey"] . "_name"),
                "eventDate" => $eventInfo["eventDate"],
                "taskName" => i18n($task["i18nKey"] . "_name"),
                "shiftName" => i18n($shift["i18nKey"] . "_name"),
                "unregisterUrl" => "{$config["baseUrl"]}?action=unregisterDialog&hash={$entry["entryHash"]}",
                "eventOrganizer" => $eventInfo["eventOrganizer"]
            ]);
            $mail->send();
        } catch (Exception $e) {
            flock($fp, LOCK_UN);
            fclose($fp);
            return MSG_REGISTER_FAILURE;
        }
        /* write user data back to json */
        $eventInfo["eventTasks"][$taskIndex]["taskShifts"][$shiftIndex]["entries"][] = $entry;
        /* write back to json file (with exclusive lock since read above) */
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($eventInfo, JSON_PRETTY_PRINT));
        fflush($fp);
        /* output message for user */
        $msg = MSG_REGISTER_SUCCESS;
    } else {
        /* no space left in this shift */
        $msg = MSG_REGISTER_NOSPACE;
    } 
    flock($fp, LOCK_UN);
    fclose($fp);
    return $msg;
}
