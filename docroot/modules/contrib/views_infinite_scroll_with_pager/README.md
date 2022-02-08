#### Overriding Pager Theme

Create file `views-infinite-scroll-with-pager.html.twig` 
in `templates` folder of your current theme.

##### Example: 

Using bootstrap pager theme for pager.

Copy file `templates/views-infinite-scroll-with-pager.html.twig`
from this module into your theme.

Replace `{% include '@system/pager.html.twig' %}`
with `{% include '@bootstrap/system/pager.html.twig' %}`
