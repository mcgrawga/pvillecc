{% extends "admin/base_site.html" %}

{% block title %}Scores{% endblock %}
{% block content %}
<div id="content-main">

Springfield Country Club is a private 18 hole golf club established in 1920. The course expanded from 9 to 18 holes when the new west nine was opened for play in the spring of 2004. The course is kept in excellent condition by Superintendent G.C. Willie.  Members enjoy the relaxed casual atmosphere in the clubhouse. The Club currently has over 300 members and memberships are available due to the expansion to 18 holes.
<br><br>
{% if scores_list %}
    <div style="float: right; margin: 0px 0px 0px 25px;">
    <table float="right" border="1" cellspacing="0" cellpadding="2">
    <tr><td colspan="5" align="center" class="RecentScoresTableTitle"><b>Recent Scores</b></td></tr>
    <tr><td class="RecentScoresTDHeader"><b>Golfer</b></td><td class="RecentScoresTDHeader"><b>Course</b></td><td class="RecentScoresTDHeader"><b>Score</b></td><td class="RecentScoresTDHeader"><b>Date</b></td></tr>
    {% for score in scores_list %}
        <tr class="{% cycle 'CourseList2' 'CourseList1'%}"><td class="RecentScoresTD">{{ score.member.fname }}</td><td class="RecentScoresTD">{{ score.tee.course.name }}</td><td class="RecentScoresTD">{{ score.score }}</td><td class="RecentScoresTD">{{ score.dateplayed|date:"M jS" }}</td></tr>
    {% endfor %}
    </table></div>
{% else %}
    <p>No scores, enter some.</p>
{% endif %}
The new west nine was designed by Mark Kerr of Kerr Golf Design and built by Ardo Schmidt Construction under Kerr's direction. They have partnered to complete a number of courses in Iowa and the Midwest.
<br><br>
The holes flow through mature trees and go out into several open areas. Players have to cross a babbling stream several times and water comes into play on seven of the nine holes.  The course has 4 sets of tees which makes it a fair challenge for players of all abilities.
</p>
</div>
{% endblock %}

