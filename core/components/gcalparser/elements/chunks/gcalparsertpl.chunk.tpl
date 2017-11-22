<div class="event-item">
    <div class="date-wrapper">
        <h3>[[+date_start:strtotime:date=`%e <span>%b</span>`]]</h3>
    </div>
    <div class="event-info">
        <h6>
            <a href="[[+event_url]]" onclick="window.open('[[+event_url]]','','width=450,height=450,left='+(screen.availWidth/2-225)+',top='+(screen.availHeight/2-225)+'');return false;" target="_blank">
                <span>[[+summary]]</span>
            </a>
        </h6>
        <span class="time">[[+all_day:is=`1`:then=`All Day`:else=`[[+start_hour]]`]]</span>
        <span class="location">[[+calendar]] • [[+location]]</span>
    </div>
</div>