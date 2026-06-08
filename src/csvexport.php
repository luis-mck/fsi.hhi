<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function handleCsvExport($config, &$eventInfo) {
    /* generate entry csv */
    $colDelim = ";";
    $rowDelim = "\n";
    $result = implode($colDelim, array(
        i18n("export.csv.header.task"),
        i18n("export.csv.header.shift"),
        i18n("export.csv.header.name"),
        i18n("export.csv.header.mail"),
        i18n("export.csv.header.timestamp")
    )) . $rowDelim;
    foreach($eventInfo["eventTasks"] as $taskIndex => $task) {
        foreach($task["taskShifts"] as $shiftIndex => $shift) {
            if( ! isset($shift["entries"])) continue;
            foreach($shift["entries"] as $entryIndex => $entry) {
                $result .= html_entity_decode(i18n($task["i18nKey"] . "_name")) . $colDelim;
                $result .= html_entity_decode(i18n($shift["i18nKey"] . "_name")) . $colDelim;
                $result .= html_entity_decode($entry["entryName"]) . $colDelim;
                $result .= html_entity_decode($entry["entryMail"] ?? "") . $colDelim;
                $result .= date(DATE_ATOM, $entry["entryTimestamp"] ?? 0);
                $result .= $rowDelim;
            }
        }
    }
    /* generate pdf file */
    $binaryPdf = exportPdfAsString($config, $eventInfo);
    /* generate admin mail */
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
        $mail->addAddress($config["adminMail1"], $config["adminName1"]);
        if (!empty($config["adminMail2"]) && filter_var($config["adminMail2"], FILTER_VALIDATE_EMAIL)) {
            $mail->addCC($config["adminMail2"], $config["adminName2"]);}
        if (!empty($config["adminMail3"]) && filter_var($config["adminMail3"], FILTER_VALIDATE_EMAIL)) {
            $mail->addCC($config["adminMail3"], $config["adminName3"]);}
        $mail->CharSet = "UTF-8";
        /* content */
        $mail->isHTML(false);
        $mail->Subject = i18n("export.mail.subject", ["eventName" => i18n($eventInfo["eventI18nKey"] . "_name")]);
        $mail->Body = i18n("export.mail.body");
        $mail->AddStringAttachment($result, i18n("export.csv.filename"));
        $mail->AddStringAttachment($binaryPdf, i18n("export.pdf.filename"));
        $mail->send();
        echo i18n("export.status.success");
    } catch (Exception $e) {
        echo i18n("export.status.error");
    }
}
