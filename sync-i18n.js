const fs = require('fs');

// 1. Quelldateien einlesen (die festen Konstanten und die Schichten)
const shiftsData = JSON.parse(fs.readFileSync('./shifts.json', 'utf8'));
const baseDe = JSON.parse(fs.readFileSync('./i18n/de.base.json', 'utf8'));
const baseEn = JSON.parse(fs.readFileSync('./i18n/en.base.json', 'utf8'));

// 2. Neue Ziel-Objekte erstellen (Kopie der Konstanten als Startpunkt)
// Der Spread-Operator (...) kopiert alle Inhalte aus baseDe in das neue Objekt
const finalDe = { ...baseDe };
const finalEn = { ...baseEn };

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
});

// 4. Die finalen, zusammengefassten Dateien generieren
fs.writeFileSync('./i18n/de.json', JSON.stringify(finalDe, null, 4));
fs.writeFileSync('./i18n/en.json', JSON.stringify(finalEn, null, 4));

console.log('🚀 Erfolgreich: de.json und en.json wurden aus den Basis-Dateien und shifts.json neu generiert!');