import json

def read_json(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        return json.load(f)

# read source data (constant base.json and variable shifts.json)
shiftsData = read_json('./shifts.json')
baseDe = read_json('./i18n/de.base.json')
baseEn = read_json('./i18n/en.base.json')

# copy base.json
finalDe = baseDe.copy()
finalEn = baseEn.copy()

event_i18n_key = shiftsData.get("eventI18nKey")
event_translations = shiftsData.get("eventTranslations")
if event_i18n_key and event_translations:
    # event name
    trans_name = event_translations.get('name')
    if trans_name:
        if trans_name.get("de"): finalDe[f"{event_i18n_key}_name"] = trans_name['de']
        if trans_name.get("en"): finalEn[f"{event_i18n_key}_name"] = trans_name['en']
    
    # event descriptions
    trans_desc = event_translations.get('desc')
    if trans_desc:
        if trans_desc.get("de"): finalDe[f"{event_i18n_key}_desc"] = trans_desc['de']
        if trans_desc.get("en"): finalEn[f"{event_i18n_key}_desc"] = trans_desc['en']

# add variable data from shifts.json
eventTasks = shiftsData.get('eventTasks', [])
for task in eventTasks:
    # add tasks
    task_i18n_key = task.get("i18nKey")
    task_translations = task.get("translations")
    if task_i18n_key and task_translations:
        # task name
        trans_name = task_translations.get("name")
        if trans_name:
            if trans_name.get("de"): finalDe[f"{task_i18n_key}_name"] = trans_name["de"]
            if trans_name.get("en"): finalEn[f"{task_i18n_key}_name"] = trans_name["en"]

        # task description
        trans_desc = task_translations.get("desc")
        if trans_desc:
            if trans_desc.get("de"): finalDe[f"{task_i18n_key}_desc"] = trans_desc["de"]
            if trans_desc.get("en"): finalEn[f"{task_i18n_key}_desc"] = trans_desc["en"]


    taskShifts = task.get("taskShifts", [])
    for shift in taskShifts:
        # add shifts
        shift_i18n_key = shift.get("i18nKey")
        shift_translations = shift.get("translations")
        if shift_i18n_key and shift_translations:
                # shift name
                trans_name = shift_translations.get("name")
                if trans_name:
                    if trans_name.get("de"): finalDe[f"{shift_i18n_key}_name"] = trans_name["de"]
                    if trans_name.get("en"): finalEn[f"{shift_i18n_key}_name"] = trans_name["en"]

                # shift description
                trans_desc = shift_translations.get("desc")
                if trans_desc:
                    if trans_desc.get("de"): finalDe[f"{shift_i18n_key}_desc"] = trans_desc["de"]
                    if trans_desc.get("en"): finalEn[f"{shift_i18n_key}_desc"] = trans_desc["en"]

with open('./i18n/de.json', 'w', encoding='utf-8') as f:
    json.dump(finalDe, f, indent=4, ensure_ascii=False)
with open('./i18n/en.json', 'w', encoding='utf-8') as f:
    json.dump(finalEn, f, indent=4, ensure_ascii=False)

print('success: translation files updated')