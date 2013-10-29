================================================================	
                          _ _    _     _                  
                         | | |  (_)   | |                 
      _ __ ___   __ _  __| | | ___  __| |  ___  _ __ __ _ 
     | '_ ` _ \ / _` |/ _` | |/ / |/ _` | / _ \| '__/ _` |
     | | | | | | (_| | (_| |   <| | (_| || (_) | | | (_| |
     |_| |_| |_|\__,_|\__,_|_|\_\_|\__,_(_)___/|_|  \__, |
                                                     __/ |
                                    //README.txt    |___/ 

================================================================

   Thank you for downloading this template from madkid.org. 


=======================
 Copyright Information
=======================

Firstly, I would like to ask you to leave all copyrights to 
madkid.org in their original state without making any changes. 
I am supplying you with a template for free, and this is all I 
ask for in return. 

If you would like to use the template without the copyrights on 
it, you can do so for a small fee of £4. Please make this 
payment to mitchsatchwell@madkid.org and specify which template 
it is you wish to use so that I can send you it without the 
copyright notices on it. 

==================
 Using The Banner
==================

In the 'img' folder there is a PSD file that you can use to edit
the banner text using Adobe Photoshop. If you do not have the 
font the banner uses installed (Script MT Bold) you will need to 
install it using the font file located in this folder. 

To install a font in Windows, go to control panel and double 
click on 'Fonts'. Once in there click on 'File' (top left) and 
then select 'Install New Font' and a little window should come 
up. At the left of that window where it says 'Folders:', 
underneath that you need to locate to the folder where the font 
is you wish to install (you double click on a folder to expand 
it). Once you have found the folder, click on it once so that it 
is highlighted. Then at the top where it says 'List of fonts' 
the font should appear. Highlight the font and click 'OK'. 

If you do not have Adobe Photoshop, you could open the blank 
banner in another image editing application and add text to the 
banner that way. 

================
 Adding Content
================

When adding your content to the layout, it is important for you 
to follow the way it is added shown in the original HTML file so 
that the content is spaced out properly. 

While adding content to the left side bar, you must follow the 
below format for each section: 

<h2>Title</h2>
<div class="hr">
<hr />
</div>
<p class="side">Section content</p>
<br />

The <br /> tag at the end of that code is not needed if it is 
the section at the bottom of the column. 

A similar format should be followed when adding content to the 
main section:

<h1>Title</h1>
<p>Text goes here</p>
<div class="clear"></div>

Likewise, the <div class="clear"></div> part is only needed 
between 2 parts and is not needed on the final one. 

The layout is coded in a way that relies on the main content 
being taller than the side content. If this is not the case, the 
side content will go over the edge of the layout. To avoid this, 
if your main content is not taller than your side content, you 
need to add a value for the minimum height of the side content, 
and this will resolve the problem. Open up style.css and scroll 
down towards the bottom and you will see the following under 
main: 
min-height: 0px; 
You will need to replace 0px with the height of the side content 
which you will need to measure in an imaging editing program 
by taking a screenshot. 

===========
 Footnote
===========

Please visit madkid.org for other templates as well as tutorials 
and other webmaster resources. 