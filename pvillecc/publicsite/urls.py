from django.conf.urls.defaults import *


# Uncomment the next two lines to enable the admin:
from django.contrib import admin
admin.autodiscover()

urlpatterns = patterns('',
    # Example:
    # (r'^publicsite/', include('publicsite.foo.urls')),

    # Uncomment the admin/doc line below and add 'django.contrib.admindocs' 
    # to INSTALLED_APPS to enable admin documentation:
    #(r'^admin/doc/', include('django.contrib.admindocs.urls')),
    (r'^$', 'publicsite.tools.views.index'),
    (r'^index$', 'publicsite.tools.views.index'),
    #(r'^rates$', 'publicsite.tools.views.rates'),
    (r'^calendar$', 'publicsite.tools.views.calendar'),
    (r'^calendar/(?P<year>\d+)$', 'publicsite.tools.views.calendar'),
    (r'^newsletters$', 'publicsite.tools.views.newsletters'),
    (r'^newsletters/(?P<year>\d+)$', 'publicsite.tools.views.newsletters'),
    (r'^news$', 'publicsite.tools.views.newsitem'),
    (r'^news/(?P<year>\d+)$', 'publicsite.tools.views.newsitem'),
    #(r'^contact$', 'publicsite.tools.views.contact'),
    (r'^printscorehistory$', 'publicsite.tools.views.printscorehistory'),
    (r'^printhandicaphistory$', 'publicsite.tools.views.printhandicaphistory'),
    (r'^scores$', 'publicsite.tools.views.scores'),
    (r'^instructions$', 'publicsite.tools.views.instructions'),
    (r'^support$', 'publicsite.tools.views.support'),
    (r'^clubdocuments$', 'publicsite.tools.views.clubdocuments'),
    #(r'^(?P<path>.*)$', 'django.views.static.serve', {'document_root': 'c:/apache2.2/htdocs/media'}),
    #(r'^img/(?P<path>.*)$', 'django.views.static.serve', {'document_root': 'c:/apache2.2/htdocs/media/sgimages'}),
    
    
    
    # Uncomment the next line to enable the admin:
     #(r'^golfcourse/publicsite/admin/(.*)', admin.site.root),
     (r'^admin/(.*)', admin.site.root),
)
