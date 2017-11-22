# gcalparser

Add a Calendar V3 API key, https://console.developers.google.com/, to `gcalparser.key` system setting to get started.

Call the snippet uncached, or with pdoPage, to get a list of upcoming events from a specified public Google calendar. Additionally, you can use commas to separate multiple calendars.

E.g. gcalparser
```$xslt
[[!gcalparser?
    &calendars=`[[*CalendarID]]`
    &limit=`5`
]]
```

E.g. pdoPage
```$xslt
<div id="pdopage">
    <div class="rows">
        [[!pdoPage?
            &element=`gcalparser`
            &calendars=`[[*CalendarID]]`
            &limit=`8`
            &totalVar=`page.total`
            &ajaxMode=`default`
        ]]
    </div>
    [[!+page.nav]]
</div>
```