from django.db import models

class Quote(models.Model):
    id = models.IntegerField(primary_key=True)
    quote = models.TextField()
    author = models.CharField(max_length=180, blank=True)
    class Meta:
        db_table = u'quote_tbl'


class Season(models.Model):
    YEAR_CHOICES = (
            (2008, '2008'),
            (2009, '2009'),
            (2010, '2010'),
            (2011, '2011'),
            (2012, '2012'),
            (2013, '2013'),
            (2014, '2014'),
            (2015, '2015'),
            (2016, '2016'),
        )
    year = models.IntegerField("Year", choices=YEAR_CHOICES)
    start = models.DateField("First Day of Season")
    end = models.DateField("Last Day of Season")
    def __unicode__(self):
        return str(self.year) 

class Member(models.Model):
    id = models.IntegerField(primary_key=True, editable=False)
    password = models.CharField(max_length=120)
    email = models.CharField(max_length=240)
    CREATEDT = models.DateTimeField(editable=False, auto_now_add=True)
    fname = models.CharField("First Name", max_length=135)
    lname = models.CharField("Last Name", max_length=135)
    GENDER_CHOICES = (
        ('M', 'Male'),
        ('F', 'Female'),
    )
    gender = models.CharField("Gender", max_length=1, choices=GENDER_CHOICES)
    city = models.CharField("City", blank=True, max_length=135)
    state = models.CharField("State", blank=True, max_length=135)
    zip = models.CharField("Zip", blank=True, max_length=135)
    country = models.CharField("Country", blank=True, max_length=135)
    street = models.CharField("Street", blank=True, max_length=135)
    class Meta:
        db_table = 'user_tbl'
    def __unicode__(self):
        return self.email 
        
class Handicap(models.Model):
    id = models.IntegerField(primary_key=True)
    num = models.DecimalField(null=True, max_digits=5, decimal_places=1, blank=True)
    dt = models.DateTimeField()
    member = models.ForeignKey(Member, db_column='userid')
    label = models.CharField(null=True, max_length=45)
    class Meta:
        db_table = u'handicap_tbl'     

class State(models.Model):
    id = models.IntegerField(primary_key=True)
    abbr = models.CharField("Abbreviation", max_length=30)
    name = models.CharField("Full Name", max_length=300)
    class Meta:
        db_table = u'state_tbl'        
    def __unicode__(self):
        return self.abbr
        
class Course(models.Model):
    id = models.IntegerField(editable=False, primary_key=True)
    userid = models.IntegerField(editable=False, blank=True)
    name = models.CharField("Name", max_length=180)
    city = models.CharField("City", max_length=180)
    state = models.ForeignKey(State, db_column='state')
    groupcourse = models.BooleanField("Give this course to everybody.", blank=True)
    createdt = models.DateTimeField(editable=False, auto_now_add=True)
    class Meta:
        db_table = u'course_tbl'
    def __unicode__(self):
        return self.name

class SupportInformation(models.Model):
    name = models.CharField('Name',max_length=250)
    street = models.CharField('Street Address',max_length=250)
    city = models.CharField('City',max_length=250)
    state = models.CharField('State',max_length=250)
    zip = models.CharField('Zip Code',max_length=25)
    phone = models.CharField('Phone Number', max_length=25)
    email = models.EmailField('Email')
    class Meta:
        ordering = ['-name']
    def __unicode__(self):
        return self.name
        
class HandicapRevisionDate(models.Model):
    dt = models.DateField("Revision Date")
    class Meta:
        ordering = ['-dt']
    def __unicode__(self):
        return str(self.dt)

class Newsletter(models.Model):
    #title = models.CharField('Title',max_length=200)
    dt = models.DateField("Creation Date")
    doc = models.FileField('Document', upload_to='uploads/newsletters/%Y/%m/%d')
    class Meta:
        ordering = ['-dt']
    def __unicode__(self):
        return str(self.dt)
        
class ClubDocument(models.Model):
    title = models.CharField('Title',max_length=200)
    doc = models.FileField('Document', upload_to='uploads/clubdocs/%Y/%m/%d')
    class Meta:
        ordering = ['title']
    def __unicode__(self):
        return self.title
        
class Event(models.Model):
    title = models.CharField('Title',max_length=200)
    desc = models.TextField('Description', blank=True)
    descdoc = models.FileField('Description Document', upload_to='uploads/events/desc/%Y/%m/%d', blank=True, null=True)
    dt = models.DateField('Date')
    tm = models.TimeField('Start Time')
    results = models.TextField('Results',null=True, blank=True)
    resultsdoc = models.FileField('Results Document', upload_to='uploads/events/results/%Y/%m/%d', blank=True, null=True)
    class Meta:
        ordering = ['dt']
    def __unicode__(self):
        return self.title
        
class NewsItem(models.Model):
    title = models.CharField('Title',max_length=200)
    desc = models.TextField('Description')
    dt = models.DateField('Date')
    class Meta:
        ordering = ['-dt']
    def __unicode__(self):
        return self.title

class HomePage(models.Model):
    title = models.CharField('Title',max_length=200)
    text = models.TextField('Text')
    def __unicode__(self):
        return self.title        

class Tee(models.Model):
    id = models.IntegerField(primary_key=True)
    course = models.ForeignKey(Course, db_column='courseid')
    name = models.CharField(max_length=180)
    class Meta:
        db_table = u'tee_tbl'

        
class OfficialScore(models.Model):
    total = models.IntegerField()
    slope = models.IntegerField()
    rating = models.FloatField()
    type = models.CharField(max_length=45, null=True)
    dateplayed = models.DateTimeField()
    member = models.ForeignKey(Member, db_column='userid')
    def __unicode__(self):
        return str(self.total)

class Score(models.Model):
    id = models.IntegerField(primary_key=True)
    member = models.ForeignKey(Member, db_column='userid')
    tee = models.ForeignKey(Tee, db_column='teeid')
    officialscore = models.ForeignKey(OfficialScore, null=True, blank=True, db_column='officialscoreid')
    type = models.CharField(null=True, db_column='type', max_length=45)
    dateplayed = models.DateTimeField()
    class Meta:
        db_table = u'score_tbl'
