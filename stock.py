import urllib
import urllib2
from bs4 import BeautifulSoup, Comment
import smtplib
import sched, time
import sys
import platform

tripwire = False
firstrun = True
s = sched.scheduler(time.time, time.sleep)
print "checking stock...."

def checkit(sc):
    global tripwire, firstrun
    #1070ti url:https://www.nvidia.com/en-us/geforce/products/10series/geforce-gtx-1070-ti/
    url = 'https://www.nvidia.com/en-us/geforce/products/10series/geforce-gtx-1080-ti/'
    hdr = {'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/604.5.6 (KHTML, like Gecko) Version/11.0.3 Safari/604.5.6',
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'}
    req = urllib2.Request(url, headers=hdr)
    response = urllib2.urlopen(req)
    the_page = response.read()
    parsed_html = BeautifulSoup(the_page, 'html.parser')
    div = parsed_html.find_all('div', class_='js-product-item')
    
    for tag in div:
        thezero = tag.find_all('div', class_='disable-out-of-stock')
        for tag in thezero:
            if tag.text == "1":
                print "diable changed: " + tag.text
                tripwire = True
    if tripwire:
        divchild = div[0].find_all('div', class_='js-in-stock')
        print "status changed: " + divchild[0].p.text
        sendit(divchild[0].p.text)
    if firstrun:
        #sendit("VM is up and ready: " + platform.platform() + " " + platform.version() + platform.machine())
        #sys.exit()
        divchild = div[0].find_all('div', class_='js-out-of-stock')
        fullstr = "current status: " + divchild[0].p.text
        print fullstr
        sendit(fullstr)
        firstrun = False
    # changes to class="js-in-stock"
    #'<p class="js-out-of-stock__without-date" style="display: inline-block;">Out of Stock.</p>'
    #<div class="disable disable-out-of-stock">0</div>
    
    if not tripwire:
        s.enter(5, 1, checkit, (sc,))

def sendit(snippit):
    server = smtplib.SMTP('smtp.gmail.com', 587)
    server.set_debuglevel(10)
    server.starttls()
    server.login("pyth0nmon1tornv@gmail.com", "mmHHmm1!")
    recips = ['9407278078@vtext.com', '9407368228@tmomail.net']
    sender = "pyth0nmon1tornv@gmail.com"
    subject = 'Subject: Stock Status Change'

    msg = ("From: %s\r\n" % sender
            + "To: %s\r\n" % recips
            + "Subject: %s\r\n" % subject
            + "\r\n"
            + snippit)
    server.sendmail(sender, recips, msg)
    server.quit()
    print "notification sent"

s.enter(5, 1, checkit, (s,))
s.run()
