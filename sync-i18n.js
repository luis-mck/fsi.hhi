const fs = require('fs');

// 1. Quelldateien einlesen (die festen Konstanten und die Schichten)
const shiftsData = JSON.parse(fs.readFileSync('./shifts.json', 'utf8'));
const baseDe = JSON.parse(fs.readFileSync('./i18n/de.base.json', 'utf8'));
const baseEn = JSON.parse(fs.readFileSync('./i18n/en.base.json', 'utf8'));

// 2. Neue Ziel-Objekte erstellen (Kopie der Konstanten als Startpunkt)
const finalDe = { ...baseDe };
const finalEn = { ...baseEn };

if (shiftsData.eventI18nKey && shiftsData.eventTranslations) {
    // Event Name
    if (shiftsData.eventTranslations.name) {
        if (shiftsData.eventTranslations.name.de) finalDe[`${shiftsData.eventI18nKey}_name`] = shiftsData.eventTranslations.name.de;
        if (shiftsData.eventTranslations.name.en) finalEn[`${shiftsData.eventI18nKey}_name`] = shiftsData.eventTranslations.name.en;
    }
    // Event Beschreibung
    if (shiftsData.eventTranslations.desc) {
        if (shiftsData.eventTranslations.desc.de) finalDe[`${shiftsData.eventI18nKey}_desc`] = shiftsData.eventTranslations.desc.de;
        if (shiftsData.eventTranslations.desc.en) finalEn[`${shiftsData.eventI18nKey}_desc`] = shiftsData.eventTranslations.desc.en;
    }
}

// 3. Variable Daten aus den Schichten hinzufügen
shiftsData.eventTasks.forEach(task => {
    if (task.i18nKey && task.translations) {
        
        // Task Name
        if (task.translations.name) {
            if (task.translations.name.de) finalDe[`${task.i18nKey}_name`] = task.translations.name.de;
            if (task.translations.name.en) finalEn[`${task.i18nKey}_name`] = task.translations.name.en;
        }

        // Task Beschreibung
        if (task.translations.desc) {
            if (task.translations.desc.de) finalDe[`${task.i18nKey}_desc`] = task.translations.desc.de;
            if (task.translations.desc.en) finalEn[`${task.i18nKey}_desc`] = task.translations.desc.en;
        }
    }

    // Schichten innerhalb des aktuellen Tasks durchlaufen
    if (task.taskShifts) {
        task.taskShifts.forEach(shift => {
            if (shift.i18nKey && shift.translations) {
                
                // Schicht Name (z.B. "10:00 - Ende (mit Auto)")
                if (shift.translations.name) {
                    if (shift.translations.name.de) finalDe[`${shift.i18nKey}_name`] = shift.translations.name.de;
                    if (shift.translations.name.en) finalEn[`${shift.i18nKey}_name`] = shift.translations.name.en;
                }

                // Schicht Beschreibung (für den optionalen blauen Chip)
                if (shift.translations.desc) {
                    if (shift.translations.desc.de) finalDe[`${shift.i18nKey}_desc`] = shift.translations.desc.de;
                    if (shift.translations.desc.en) finalEn[`${shift.i18nKey}_desc`] = shift.translations.desc.en;
                }
            }
        });
    }
});

// 4. Die finalen, zusammengefassten Dateien generieren
fs.writeFileSync('./i18n/de.json', JSON.stringify(finalDe, null, 4));
fs.writeFileSync('./i18n/en.json', JSON.stringify(finalEn, null, 4));

console.log('🚀 Erfolgreich: de.json und en.json wurden aus den Basis-Dateien und shifts.json neu generiert!');