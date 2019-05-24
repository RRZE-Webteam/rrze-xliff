# rrze-xliff
Export und Import von WordPress Content zu und vom [XLIFF (Version 2)](http://docs.oasis-open.org/xliff/xliff-core/v2.0/os/xliff-core-v2.0-os.html) Format.

Status dieses Plugins: **In Planung.**

## Beschreibung

Deses Plugin soll bei Multisite-Installationen von WordPress zum Einsatz kommen. Es ermöglicht den Export von Seiten in eine Datei im XLIFF-Format, die danach entweder heruntergalden oder zu einer orkonfigurierten E-Mailadresse gesandt werden kann.
ZUsätzlich soll es eine Import-Funktion geben, bei der Inhalte wieedrum importiert werden können.

Die Funktion ist nur im Backend bei der Bearbeitung von definierten Post-Types für Userrollen mit Schreibberechtigung sichtbar. 

 
## Kompatibilität zu anderen Plugins und Themes

### RRZE Workflow

Das Plugin soll die bisherige Funktionalität im WordPress Plugin [CMS Workflow](https://github.com/RRZE-Webteam/cms-workflow) ersetzen und verbessern.

## Besonderheiten

### Metadata-Import im Block-Editor

Damit der Import von Metadaten im Block-Editor funktioniert, müssen die Metadaten vorher über `register_post_meta()` registriert und dabei mit `'show_in_rest' => true` in der REST-API sichtbar gemacht werden. Andernfalls werden die Metadaten nicht importiert.
