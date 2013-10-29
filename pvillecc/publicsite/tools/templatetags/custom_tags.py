from django import template
from publicsite.tools.models import Quote
register = template.Library()

def get_random_quote():
    q = Quote.objects.order_by('?')[0]
    txt = q.quote
    if q.author:
        txt += "  "
        txt += q.author
    return txt

register.simple_tag(get_random_quote)
