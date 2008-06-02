# vim: ts=4
import wx
import twain
import StringIO
import Image
import ImageFilter
import os
from datetime import *
from reportlab.pdfgen import canvas
from wxPython.wx import *
from wxPython.lib.dialogs import *
from wx import calendar
import re
import time
import sys
import codecs
from threading import Thread
import mozillaemulator

class upload(Thread):
   def __init__ (self,filename,id):
      Thread.__init__(self)
      self.filename = filename
      self.idn = id
      self.status = -1
   def run(self):
      dl = mozillaemulator.MozillaEmulator()           
      fdata = codecs.open(self.filename,"rb","ISO-8859-1").read()
      print "UPLOADING %s with ID %s" % (self.filename,self.idn)
      print dl.post_multipart('http://active.dcarf/quexf/upload/import.php',[('descr',self.idn)],[('form','data.pdf',fdata)])
      self.status = 1


class ScanTool(wx.App):
	def OnInit(self):
		while True:
			window = ScanWindow(None)

			result = window.ShowModal()
			
			if result == wx.ID_OK:
				id = window.id
				window.onClose(wx.ID_CANCEL)
				#self.write_database(id)
			elif result == wx.ID_CANCEL:
				return False	

		return True
	
class ScanWindow(wx.Dialog):
	""" This brings up a window to receive the barcode scanner input """
	def __init__(self, parent):
		wx.Dialog.__init__(self, parent, wx.ID_ANY, title = "Scan Processor", \
				pos = (300, 300), style = wx.CAPTION | wx.STAY_ON_TOP)

		self.sizer = wx.BoxSizer(wx.VERTICAL)

		self.text2 = wx.StaticText(self, wx.ID_ANY, "Please enter number of sides to scan:")
		self.control2 = wx.TextCtrl(self, wx.ID_ANY, size = (200, -1))
		self.text4 = wx.StaticText(self, wx.ID_ANY, "Please select page size:")
		self.radio1 = wx.RadioBox(self,wx.ID_ANY,choices=['A3','A4'])
		self.radio1.SetSelection(0)
		self.text5 = wx.StaticText(self, wx.ID_ANY, "Please select duplex or single sided:")
		self.radio2 = wx.RadioBox(self,wx.ID_ANY,choices=['Duplex','Single Sided'])
		self.radio2.SetSelection(0)
		self.text6 = wx.StaticText(self, wx.ID_ANY, "Upload to queXF?")
		self.radio3 = wx.RadioBox(self,wx.ID_ANY,choices=['No','Yes upload'])
		self.radio3.SetSelection(0)
		self.text3 = wx.StaticText(self, wx.ID_ANY, "Please enter project prefix:")
		self.control3 = wx.TextCtrl(self, wx.ID_ANY, size = (200, -1))
		self.text = wx.StaticText(self, wx.ID_ANY, "Please scan barcode:")
		self.control = wx.TextCtrl(self, wx.ID_ANY, size = (200, -1), \
				style = wx.TE_PROCESS_ENTER)
		self.button = wx.Button(self, wx.ID_CANCEL, label = "Cancel")

		self.sizer.Add(self.text2, 0, wx.ALL, 10)
		self.sizer.Add(self.control2, 1, wx.ALL, 10)
		self.sizer.Add(self.text4, 0, wx.ALL, 10)
		self.sizer.Add(self.radio1,0,wx.ALL,10)
		self.sizer.Add(self.text5, 0, wx.ALL, 10)
		self.sizer.Add(self.radio2,0,wx.ALL,10)
		self.sizer.Add(self.text6, 0, wx.ALL, 10)
		self.sizer.Add(self.radio3,0,wx.ALL,10)
		self.sizer.Add(self.text3, 0, wx.ALL, 10)
		self.sizer.Add(self.control3, 1, wx.ALL, 10)
		self.sizer.Add(self.text, 0, wx.ALL, 10)
		self.sizer.Add(self.control, 1, wx.ALL, 10)
		self.sizer.Add(self.button, 0, wx.ALL, 10)


		self.SetSizer(self.sizer)
		self.SetAutoLayout(1)
		self.sizer.Fit(self)

		self.manager = None
		self.scanner = None
		self.id = None
		self.numPages = None

		wx.EVT_TEXT_ENTER(self, wx.ID_ANY, self.scan)
		wx.EVT_CLOSE(self, self.onClose)

		self.threads = []

	def scan(self, event):
		self.id = self.control.GetValue()

		self.numPages = int(self.control2.GetValue())

		#if self.numPages < 1:
		#	self.control.SetValue("")
		#	return

		if not self.manager:
			self.manager = twain.SourceManager(self.GetHandle())
			if not self.manager:
				return

			self.manager.SetCallback(self.onEvent)

		if not self.scanner:
			self.scanner = self.manager.OpenSource("Network ScanGear Ver.2.1")
			if not self.scanner:
				return

		try:
				if self.radio1.GetSelection() == 0:
					self.scanner.SetCapability(twain.ICAP_SUPPORTEDSIZES, 4, 11) # A3 pages
				else:
					self.scanner.SetCapability(twain.ICAP_SUPPORTEDSIZES, 4, 1) # A4 pages
		except:
				#close and start again
				self.manager.destroy()
				self.manager = None
				self.manager = twain.SourceManager(self.GetHandle())
				self.manager.SetCallback(self.onEvent)
				self.scanner.destroy()
				self.scanner = None
				self.scanner = self.manager.OpenSource("Network ScanGear Ver.2.1")
				if self.radio1.GetSelection() == 0:
					self.scanner.SetCapability(twain.ICAP_SUPPORTEDSIZES, 4, 11) # A3 pages
				else:
					self.scanner.SetCapability(twain.ICAP_SUPPORTEDSIZES, 4, 1) # A4 pages

		if self.radio2.GetSelection() == 0:
			self.scanner.SetCapability(twain.CAP_DUPLEXENABLED,4,1)
		else:
			self.scanner.SetCapability(twain.CAP_DUPLEXENABLED,4,0)
		#self.scanner.SetCapability(twain.CCAP_CEI_SCANSPEED, 4, 1) # Scanning speed
		#self.scanner.SetCapability(0x8001, 2, 1) # Skip blank pages
		self.scanner.RequestAcquire(False)
	

	def processTransfer(self, event):
		directory = "\\\\files.dcarf\\teleactive\\PDFs\\To be processed\\"
		#directory = "H:\\Desktop\\"
		#temp_directory = "\\\\files.dcarf\\tmp\\MailTracking\\"
		#from time import strftime,localtime
		#filename = strftime("%Y-%m-%d-%H-%M-%S",localtime()) + ".pdf"

		#if os.access(directory, os.W_OK):
		#	c = canvas.Canvas(directory + filename)
		#elif os.access(temp_directory, os.W_OK):
		#	print "Unable to write to teleactive directory. Writing output " + \
		#			"to " + temp_directory
		#	c = canvas.Canvas(temp_directory + filename)
		#else:
		#	print "Unable to write output file. Terminating scan"
		#	return

		#c.setPageCompression(1)
		
		remaining = 1	

		if self.control.GetValue() == "":
			from time import strftime,localtime
			self.control.SetValue(strftime("%Y-%m-%d-%H-%M-%S",localtime()))

		val = self.control3.GetValue() + self.control.GetValue()
		count = 0
		c = 0
		filename = ""

		#print self.calendar.GetDate()

		while remaining > 0:
			#print "Remaining gt 0 : %s" % remaining
			val = self.control3.GetValue() + self.control.GetValue()
			count = 0
			c = 0	
			self.numPages = int(self.control2.GetValue())

			while self.numPages > 0:
				count = count + 1

				#print "COUNT: %s PAGES: %s REMAINING: %s" % (count,self.numPages,remaining)
				#fn = temp_directory + "tmp" + str(count) + ".gif"
				#tmp_string = StringIO.StringIO()
				(handle, remaining) = self.scanner.XferImageNatively()
				
				#print "handle %s" % handle
				#print "remaining %s" % remaining
				
				#twain.DIBToBMFile(handle, fn) 
				#Image.open(StringIO.StringIO(twain.DIBToBMFile(handle))).save( \
				#		fn, "GIF")
				temp_img = Image.open(StringIO.StringIO(twain.DIBToBMFile(handle)))
				(width, height) = temp_img.size


				if self.radio1.GetSelection() == 0: #A3 - so split page
					a4a = temp_img.crop((0,0,int(width)/2,height))
					a4a.load()		
						
					a4b = temp_img.crop((int(width)/2,0,width,height))
					a4b.load()
						
					img = temp_img.resize((int(width) / 2, int(height) / 2), \
							Image.ANTIALIAS)
			
					(width, height) = a4a.size
					imga = a4a.resize((int(width) / 2, int(height) / 2), \
							Image.ANTIALIAS)
					(width, height) = a4b.size
					imgb = a4b.resize((int(width) / 2, int(height) / 2), \
							Image.ANTIALIAS)
				else: # A4 don't split
					img = temp_img.resize((int(width) / 2, int(height) / 2), \
							Image.ANTIALIAS)

	
	
				twain.GlobalHandleFree(handle)
				
				if c == 0:
					filename = val + ".pdf"
					if os.access(directory, os.W_OK):
						c = canvas.Canvas(directory + filename)		
					c.setPageCompression(1)


				if self.radio1.GetSelection() == 0: #A3 - so split page
					c.drawInlineImage(imga, 0, 0, 600, 849)
					c.showPage()
			
					c.drawInlineImage(imgb, 0, 0, 600, 849)
					c.showPage()				
				else: # A4 don't split
					c.drawInlineImage(img, 0, 0, 600, 849)
					c.showPage()				



	
				self.numPages = self.numPages - 1
	
			c.save()
			print "SAVED to %s.pdf"	% val
			if self.radio3.GetSelection() != 0:
				upload(directory + filename,self.control.GetValue()).start()
			self.control.SetValue("")



	def onEvent(self, event):
		if event == twain.MSG_XFERREADY:
			self.processTransfer(event)
			self.scanner = None
			#self.EndModal(wx.ID_OK)
		elif event == twain.MSG_CLOSEDSREQ:
			self.scanner = None
			#self.EndModal(wx.ID_OK)

	def onClose(self, event):
		if self.scanner:
			self.scanner.destroy()
		if self.manager:
			self.manager.destroy()
		del self.scanner, self.manager
		self.Destroy()

class SampleDialog(wx.Dialog):
	def __init__(self, parent, choice_list):
		wx.Dialog.__init__(self, parent, wx.ID_ANY, title = "Select Sample", \
				pos = (300, 300), style = wx.CAPTION | wx.STAY_ON_TOP)

		self.sizer = wx.BoxSizer(wx.VERTICAL)

		self.text = wx.StaticText(self, wx.ID_ANY, "Please select the " + \
				"correct sample from the list:")
		self.list = wx.Choice(self, wx.ID_ANY, choices = choice_list)
		self.button = wx.Button(self, wx.ID_OK, label = "OK")

		self.sizer.Add(self.text, 0, wx.ALL, 10)
		self.sizer.Add(self.list, 0, wx.ALL, 10)
		self.sizer.Add(self.button, 0, wx.ALL, 10)

		self.SetSizer(self.sizer)
		self.SetAutoLayout(1)
		self.sizer.Fit(self)

app = ScanTool()
#app = wx.PySimpleApp()
#frame = ScanWindow(None)
app.MainLoop()
