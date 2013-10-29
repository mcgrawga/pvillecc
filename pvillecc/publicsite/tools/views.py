from django.template import Context, loader
from django.http import HttpResponse
from publicsite.tools.models import *
from django.contrib.admin.views.decorators import staff_member_required
import csv
from datetime import date

def index(request):
    #scores_list = Score.objects.order_by('-dateplayed')[:10]
    HomePageContent = HomePage.objects.all()[0]
    ScoresList = []
    oscore_list = OfficialScore.objects.order_by('-dateplayed')[:10]
    for oscore in oscore_list: 
        fname = oscore.score_set.all()[0].member.fname[0:10]
        course = oscore.score_set.all()[0].tee.course.name[0:16]
        score = oscore.total
        dateplayed = oscore.dateplayed
        d = {'city':'Paris', 'age':38, (102,1650,1601):'A matrix coordinate'}
        ScoresList.append({'fname':fname, 'course':course, 'score':score, 'dateplayed':dateplayed})
        
    t = loader.get_template('tools/index.html')
    c = Context({'scores_list': ScoresList, 'HomePageContent':HomePageContent})
    return HttpResponse(t.render(c))


@staff_member_required
def scores(request):
    member_list = Member.objects.order_by('lname')
    t = loader.get_template('admin/scores.html')
    c = Context({'member_list': member_list})
    return HttpResponse(t.render(c))

@staff_member_required
def instructions(request):
    t = loader.get_template('admin/instructions.html')
    c = Context()
    return HttpResponse(t.render(c))
    
def support(request):
    support_info = SupportInformation.objects.all()[0]
    t = loader.get_template('admin/support.html')
    c = Context({'support_info': support_info})
    return HttpResponse(t.render(c))    
    
def detail(request, member_id):
    the_member = Member.objects.get(pk=member_id)
    t = loader.get_template('tools/detail.html')
    c = Context({'the_member': the_member})
    return HttpResponse(t.render(c))
    
def rates(request):
    t = loader.get_template('tools/rates.html')
    c = Context()
    return HttpResponse(t.render(c))
    
def calendar(request, year=""):
    current_year = date.today().year
    last_year = date.today().year - 1
    if year:
        current_year = year
    event_list = Event.objects.filter(dt__year = current_year)
    year_list = Event.objects.filter(dt__gte = date(last_year, 1, 1)).dates('dt', 'year', order='ASC')
    t = loader.get_template('tools/calendar.html')
    c = Context({'event_list': event_list, 'year_list': year_list})
    return HttpResponse(t.render(c))
    
def newsletters(request, year=""):
    current_year = date.today().year
    last_year = date.today().year - 1
    if year:
        current_year = year
    newsletter_list = Newsletter.objects.filter(dt__year = current_year)
    year_list = Newsletter.objects.filter(dt__gte = date(last_year, 1, 1)).dates('dt', 'year', order='ASC')
    t = loader.get_template('tools/newsletters.html')
    c = Context({'newsletter_list': newsletter_list, 'year_list': year_list})
    return HttpResponse(t.render(c))    
    
def clubdocuments(request):
    document_list = ClubDocument.objects.all()
    t = loader.get_template('tools/clubdocuments.html')
    c = Context({'document_list': document_list})
    return HttpResponse(t.render(c))    

def newsitem(request, year=""):
    current_year = date.today().year
    last_year = date.today().year - 1
    if year:
        current_year = year
    newsitem_list = NewsItem.objects.filter(dt__year = current_year)
    year_list = NewsItem.objects.filter(dt__gte = date(last_year, 1, 1)).dates('dt', 'year', order='ASC')
    t = loader.get_template('tools/news.html')
    c = Context({'newsitem_list': newsitem_list, 'year_list': year_list})
    return HttpResponse(t.render(c))    
    
def contact(request):
    club_list = Club.objects.all()
    t = loader.get_template('tools/contact.html')
    c = Context({'club_list': club_list})
    return HttpResponse(t.render(c))

def printscorehistory(request):
    # Create the HttpResponse object with the appropriate CSV header.
    member_list = Member.objects.all()
    response = HttpResponse(mimetype='text/csv')
    response['Content-Disposition'] = 'attachment; filename=memberhandicaps.csv'
    
    # Get the latest handicap revision date
    CurrentDate = date.today().isoformat()
    HRD = HandicapRevisionDate.objects.filter(dt__lt=CurrentDate).order_by('-dt')[0]

    writer = csv.writer(response)
    writer.writerow(['FIRST NAME', 'LAST NAME', 'HANDICAP', 'REVISION DATE', "SCORE 1", "SCORE 2", "SCORE 3", "SCORE 4", "SCORE 5", "SCORE 6", "SCORE 7", "SCORE 8", "SCORE 9", "SCORE 10", "SCORE 11", "SCORE 12", "SCORE 13", "SCORE 14", "SCORE 15", "SCORE 16", "SCORE 17", "SCORE 18", "SCORE 19", "SCORE 20" ])
    for mem in member_list: 
        handicap_list = Handicap.objects.filter(dt__lt=HRD).filter(member__id=mem.id).order_by('-dt', '-id')
        score_list = OfficialScore.objects.filter(member__id=mem.id).order_by('-dateplayed')
        #for handicap in handicap_list:
        if handicap_list:
            handicap = handicap_list[0]
            if handicap.num == None:
                #writer.writerow([mem.fname, mem.lname, "N/A", "N/A"])
                writetocsv(writer, mem.fname, mem.lname, "N/A", "N/A", score_list)
            else:
                #writer.writerow([mem.fname, mem.lname, handicap.num, handicap.dt, len(score_list)])
                writetocsv(writer, mem.fname, mem.lname, handicap.num, HRD, score_list)
        else:
            #writer.writerow([mem.fname, mem.lname, "N/A", "N/A"])
            writetocsv(writer, mem.fname, mem.lname, "N/A", "N/A", score_list)
    return response
    
def printhandicaphistory(request):
    # Create the HttpResponse object with the appropriate CSV header.
    member_list = Member.objects.all()
    response = HttpResponse(mimetype='text/csv')
    response['Content-Disposition'] = 'attachment; filename=memberhandicaps.csv'
    
    # Get the latest handicap revision date
    CurrentDate = date.today().isoformat()
    revisiondate_list = HandicapRevisionDate.objects.filter(dt__lt=CurrentDate).order_by('-dt')[:6]
    handidate1 = revisiondate_list[0].dt
    handidate2 = revisiondate_list[1].dt
    handidate3 = revisiondate_list[2].dt
    handidate4 = revisiondate_list[3].dt
    handidate5 = revisiondate_list[4].dt
    handidate6 = revisiondate_list[5].dt

    writer = csv.writer(response)
    writer.writerow(['LAST NAME', 'FIRST NAME', 'HANDICAP1', 'REVISION DATE1', 'HANDICAP2', 'REVISION DATE2', 'HANDICAP3', 'REVISION DATE3', 'HANDICAP4', 'REVISION DATE4', 'HANDICAP5', 'REVISION DATE5', 'HANDICAP6', 'REVISION DATE6' ])
    for mem in member_list: 
        if handidate1:
            if Handicap.objects.filter(dt__lt=handidate1).filter(member__id=mem.id).order_by('-dt', '-id').count() > 0:
                h = Handicap.objects.filter(dt__lt=handidate1).filter(member__id=mem.id).order_by('-dt', '-id')[0]
                h1 = h.num
            else:
                h1 = "N/A"
        else:
            h1 = "N/A"
        if handidate2:
            if Handicap.objects.filter(dt__lt=handidate2).filter(member__id=mem.id).order_by('-dt', '-id').count() > 0:
                h = Handicap.objects.filter(dt__lt=handidate2).filter(member__id=mem.id).order_by('-dt', '-id')[0]
                h2 = h.num
            else:
                h2 = "N/A"
        else:
            h2 = "N/A"
        if handidate3:
            if Handicap.objects.filter(dt__lt=handidate3).filter(member__id=mem.id).order_by('-dt', '-id').count() > 0:
                h = Handicap.objects.filter(dt__lt=handidate3).filter(member__id=mem.id).order_by('-dt', '-id')[0]
                h3 = h.num
            else:
                h3 = "N/A"
        else:
            h3 = "N/A"
        if handidate4:
            if Handicap.objects.filter(dt__lt=handidate4).filter(member__id=mem.id).order_by('-dt', '-id').count() > 0:
                h = Handicap.objects.filter(dt__lt=handidate4).filter(member__id=mem.id).order_by('-dt', '-id')[0]
                h4 = h.num
            else:
                h4 = "N/A"
        else:
            h4 = "N/A"
        if handidate5:
            if Handicap.objects.filter(dt__lt=handidate5).filter(member__id=mem.id).order_by('-dt', '-id').count() > 0:
                h = Handicap.objects.filter(dt__lt=handidate5).filter(member__id=mem.id).order_by('-dt', '-id')[0]
                h5 = h.num
            else:
                h5 = "N/A"
        else:
            h5 = "N/A"
        if handidate6:
            if Handicap.objects.filter(dt__lt=handidate6).filter(member__id=mem.id).order_by('-dt', '-id').count() > 0:
                h = Handicap.objects.filter(dt__lt=handidate6).filter(member__id=mem.id).order_by('-dt', '-id')[0]
                h6 = h.num
            else:
                h6 = "N/A"
        else:
            h6 = "N/A"
        writer.writerow([mem.lname, mem.fname, h1, handidate1, h2, handidate2, h3, handidate3, h4, handidate4, h5, handidate5, h6, handidate6])
    return response
    
def writetocsv(writer, fname, lname, handi, dt, score_list):
    numscores = len(score_list)
    if numscores > 19:
        writer.writerow([fname, lname, handi, dt, score_list[0].total, score_list[1].total, score_list[2].total, score_list[3].total, score_list[4].total, score_list[5].total, score_list[6].total, score_list[7].total, score_list[8].total, score_list[9].total, score_list[10].total, score_list[11].total, score_list[12].total, score_list[13].total, score_list[14].total, score_list[15].total, score_list[16].total, score_list[17].total, score_list[18].total, score_list[19].total])
    elif numscores > 18:
        writer.writerow([fname, lname, handi, dt, score_list[0].total, score_list[1].total, score_list[2].total, score_list[3].total, score_list[4].total, score_list[5].total, score_list[6].total, score_list[7].total, score_list[8].total, score_list[9].total, score_list[10].total, score_list[11].total, score_list[12].total, score_list[13].total, score_list[14].total, score_list[15].total, score_list[16].total, score_list[17].total, score_list[18].total])
    elif numscores > 17:
        writer.writerow([fname, lname, handi, dt, score_list[0].total, score_list[1].total, score_list[2].total, score_list[3].total, score_list[4].total, score_list[5].total, score_list[6].total, score_list[7].total, score_list[8].total, score_list[9].total, score_list[10].total, score_list[11].total, score_list[12].total, score_list[13].total, score_list[14].total, score_list[15].total, score_list[16].total, score_list[17].total])
    elif numscores > 16:
        writer.writerow([fname, lname, handi, dt, score_list[0].total, score_list[1].total, score_list[2].total, score_list[3].total, score_list[4].total, score_list[5].total, score_list[6].total, score_list[7].total, score_list[8].total, score_list[9].total, score_list[10].total, score_list[11].total, score_list[12].total, score_list[13].total, score_list[14].total, score_list[15].total, score_list[16].total])
    elif numscores > 15:
        writer.writerow([fname, lname, handi, dt, score_list[0].total, score_list[1].total, score_list[2].total, score_list[3].total, score_list[4].total, score_list[5].total, score_list[6].total, score_list[7].total, score_list[8].total, score_list[9].total, score_list[10].total, score_list[11].total, score_list[12].total, score_list[13].total, score_list[14].total, score_list[15].total])
    elif numscores > 14:
        writer.writerow([fname, lname, handi, dt, score_list[0].total, score_list[1].total, score_list[2].total, score_list[3].total, score_list[4].total, score_list[5].total, score_list[6].total, score_list[7].total, score_list[8].total, score_list[9].total, score_list[10].total, score_list[11].total, score_list[12].total, score_list[13].total, score_list[14].total])
    elif numscores > 13:
        writer.writerow([fname, lname, handi, dt, score_list[0].total, score_list[1].total, score_list[2].total, score_list[3].total, score_list[4].total, score_list[5].total, score_list[6].total, score_list[7].total, score_list[8].total, score_list[9].total, score_list[10].total, score_list[11].total, score_list[12].total, score_list[13].total])
    elif numscores > 12:
        writer.writerow([fname, lname, handi, dt, score_list[0].total, score_list[1].total, score_list[2].total, score_list[3].total, score_list[4].total, score_list[5].total, score_list[6].total, score_list[7].total, score_list[8].total, score_list[9].total, score_list[10].total, score_list[11].total, score_list[12].total])
    elif numscores > 11:
        writer.writerow([fname, lname, handi, dt, score_list[0].total, score_list[1].total, score_list[2].total, score_list[3].total, score_list[4].total, score_list[5].total, score_list[6].total, score_list[7].total, score_list[8].total, score_list[9].total, score_list[10].total, score_list[11].total])
    elif numscores > 10:
        writer.writerow([fname, lname, handi, dt, score_list[0].total, score_list[1].total, score_list[2].total, score_list[3].total, score_list[4].total, score_list[5].total, score_list[6].total, score_list[7].total, score_list[8].total, score_list[9].total, score_list[10].total])
    elif numscores > 9:
        writer.writerow([fname, lname, handi, dt, score_list[0].total, score_list[1].total, score_list[2].total, score_list[3].total, score_list[4].total, score_list[5].total, score_list[6].total, score_list[7].total, score_list[8].total, score_list[9].total])
    elif numscores > 8:
        writer.writerow([fname, lname, handi, dt, score_list[0].total, score_list[1].total, score_list[2].total, score_list[3].total, score_list[4].total, score_list[5].total, score_list[6].total, score_list[7].total, score_list[8].total])
    elif numscores > 7:
        writer.writerow([fname, lname, handi, dt, score_list[0].total, score_list[1].total, score_list[2].total, score_list[3].total, score_list[4].total, score_list[5].total, score_list[6].total, score_list[7].total])
    elif numscores > 6:
        writer.writerow([fname, lname, handi, dt, score_list[0].total, score_list[1].total, score_list[2].total, score_list[3].total, score_list[4].total, score_list[5].total, score_list[6].total])
    elif numscores > 5:
        writer.writerow([fname, lname, handi, dt, score_list[0].total, score_list[1].total, score_list[2].total, score_list[3].total, score_list[4].total, score_list[5].total])
    elif numscores > 4:
        writer.writerow([fname, lname, handi, dt, score_list[0].total, score_list[1].total, score_list[2].total, score_list[3].total, score_list[4].total])
    elif numscores > 3:
        writer.writerow([fname, lname, handi, dt, score_list[0].total, score_list[1].total, score_list[2].total, score_list[3].total])
    elif numscores > 2:
        writer.writerow([fname, lname, handi, dt, score_list[0].total, score_list[1].total, score_list[2].total])
    elif numscores > 1:
        writer.writerow([fname, lname, handi, dt, score_list[0].total, score_list[1].total])        
    elif numscores > 0:
        writer.writerow([fname, lname, handi, dt, score_list[0].total])        
    else:
        writer.writerow([fname, lname, handi, dt])
        

