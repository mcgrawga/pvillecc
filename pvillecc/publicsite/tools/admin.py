from publicsite.tools.models import *
from django.contrib import admin

from django.contrib.flatpages.models import FlatPage
from django.contrib.flatpages.admin import FlatPageAdmin as FlatPageAdminOld

class FlatPageAdmin(FlatPageAdminOld):
    class Media:
        js = ('js/tinymce/jscripts/tiny_mce/tiny_mce.js',
              'js/tinymce/jscripts/tiny_mce/textareas.js',)

# We have to unregister it, and then reregister
admin.site.unregister(FlatPage)
admin.site.register(FlatPage, FlatPageAdmin)




class MemberAdmin(admin.ModelAdmin):
    search_fields = ['email', 'fname', 'lname']
    list_display = ('lname', 'fname', 'email')
    list_filter = ['city']
admin.site.register(Member, MemberAdmin)




class HomePageAdmin(admin.ModelAdmin):
    class Media:
        js = ('js/tinymce/jscripts/tiny_mce/tiny_mce.js',
              'js/tinymce/jscripts/tiny_mce/textareas.js',)
admin.site.register(HomePage, HomePageAdmin)

class EventAdmin(admin.ModelAdmin):
    list_display = ('title', 'dt', 'tm')
    search_fields = ['title']
    list_filter = ['dt']
    fields = ['title', 'dt', 'tm', 'desc', 'descdoc', 'results', 'resultsdoc']
    class Media:
        js = ('js/tinymce/jscripts/tiny_mce/tiny_mce.js',
              'js/tinymce/jscripts/tiny_mce/textareas.js',)
admin.site.register(Event, EventAdmin)

class NewsItemAdmin(admin.ModelAdmin):
    list_display = ('title', 'dt')
    search_fields = ['title']
    list_filter = ['dt']
    class Media:
        js = ('js/tinymce/jscripts/tiny_mce/tiny_mce.js',
              'js/tinymce/jscripts/tiny_mce/textareas.js',)
admin.site.register(NewsItem, NewsItemAdmin)

#class MembershipApplicationAdmin(admin.ModelAdmin):


class ClubDocumentAdmin(admin.ModelAdmin):
    list_display = ['title']
admin.site.register(ClubDocument, ClubDocumentAdmin)

class NewsletterAdmin(admin.ModelAdmin):
    list_display = ['dt']
    list_filter = ['dt']
admin.site.register(Newsletter, NewsletterAdmin)

class CourseAdmin(admin.ModelAdmin):
    def queryset(self, request):
        qs = self.model._default_manager.get_query_set().filter(userid = 0)
        ordering = self.ordering or ()
        if ordering:
            qs = qs.order_by(*ordering)
        return qs
        
    list_display = ('name', 'city', 'state')
    list_filter = ['state']
    fields = ['name', 'city', 'state', 'groupcourse']
#admin.site.register(Course, CourseAdmin)







#admin.site.register(Club)
#admin.site.register(SupportInformation)

class SeasonAdmin(admin.ModelAdmin):
    list_display = ('year', 'start', 'end')
admin.site.register(Season, SeasonAdmin)

class HandicapRevisionDateAdmin(admin.ModelAdmin):
    list_filter = ['dt']
admin.site.register(HandicapRevisionDate, HandicapRevisionDateAdmin)


#admin.site.register(State)

