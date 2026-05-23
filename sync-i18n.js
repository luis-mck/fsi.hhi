const fs = require('fs');

// read source data (constant base.json and variable shifts.json)
const shiftsData = JSON.parse(fs.readFileSync('./shifts.json', 'utf8'));
const baseDe = JSON.parse(fs.readFileSync('./i18n/de.base.json', 'utf8'));
const baseEn = JSON.parse(fs.readFileSync('./i18n/en.base.json', 'utf8'));

// copy base.json
const finalDe = { ...baseDe };
const finalEn = { ...baseEn };

if (shiftsData.eventI18nKey && shiftsData.eventTranslations) {
    // event name
    if (shiftsData.eventTranslations.name) {
        if (shiftsData.eventTranslations.name.de) finalDe[`${shiftsData.eventI18nKey}_name`] = shiftsData.eventTranslations.name.de;
        if (shiftsData.eventTranslations.name.en) finalEn[`${shiftsData.eventI18nKey}_name`] = shiftsData.eventTranslations.name.en;
    }
    // event description
    if (shiftsData.eventTranslations.desc) {
        if (shiftsData.eventTranslations.desc.de) finalDe[`${shiftsData.eventI18nKey}_desc`] = shiftsData.eventTranslations.desc.de;
        if (shiftsData.eventTranslations.desc.en) finalEn[`${shiftsData.eventI18nKey}_desc`] = shiftsData.eventTranslations.desc.en;
    }
}

// add variable data from shifts.json
shiftsData.eventTasks.forEach(task => {
    // add tasks
    if (task.i18nKey && task.translations) {
        
        // task name
        if (task.translations.name) {
            if (task.translations.name.de) finalDe[`${task.i18nKey}_name`] = task.translations.name.de;
            if (task.translations.name.en) finalEn[`${task.i18nKey}_name`] = task.translations.name.en;
        }

        // task description
        if (task.translations.desc) {
            if (task.translations.desc.de) finalDe[`${task.i18nKey}_desc`] = task.translations.desc.de;
            if (task.translations.desc.en) finalEn[`${task.i18nKey}_desc`] = task.translations.desc.en;
        }
    }

    // add shifts
    if (task.taskShifts) {
        task.taskShifts.forEach(shift => {
            if (shift.i18nKey && shift.translations) {
                
                // shift name
                if (shift.translations.name) {
                    if (shift.translations.name.de) finalDe[`${shift.i18nKey}_name`] = shift.translations.name.de;
                    if (shift.translations.name.en) finalEn[`${shift.i18nKey}_name`] = shift.translations.name.en;
                }

                // shift description
                if (shift.translations.desc) {
                    if (shift.translations.desc.de) finalDe[`${shift.i18nKey}_desc`] = shift.translations.desc.de;
                    if (shift.translations.desc.en) finalEn[`${shift.i18nKey}_desc`] = shift.translations.desc.en;
                }
            }
        });
    }
});

// write complete translation files
fs.writeFileSync('./i18n/de.json', JSON.stringify(finalDe, null, 4));
fs.writeFileSync('./i18n/en.json', JSON.stringify(finalEn, null, 4));

console.log('🚀 Erfolgreich: de.json und en.json wurden aus den Basis-Dateien und shifts.json neu generiert!');