import os
from shutil import copy2 as copyfile
from pathlib import Path
import json

##########EDIT PATHS HERE ###################

zencart_path = "/home/daniel/Public/testLinks"
zencart_admin_folder = "zenadmin"
zencart_template_folder = "responsive_classic"

#############################################


class DevInstallBookx:

    '''
    destination directory, Admin Folder name, tpl name
    '''
    PROJECT = 'Bookx'

    VERSION = '1.0.1'

    PROJ_EXCLUDE_DIR = ('.git', 'test')

    PROJ_INSTALLATION_FOLDER = 'ZC_INSTALLATION'

    PROJ_ADMIN_FOLDER = '[RENAME_TO_YOUR_ADMIN_FOLDER]'

    adminFiles = []

    catalogFiles = []

    def __init__(self, destDir, adminDirName, tplDirName):

        self.projPathRoot = self.script_path()
        # project files
        self.projPathInstallFolder = self.script_path().joinpath(
            self.PROJ_INSTALLATION_FOLDER)
        self.projPathAdminFiles = self.projPathInstallFolder.joinpath(
            self.PROJ_ADMIN_FOLDER)

        # zc dest path
        self.zcPath = Path(destDir)
        # zc dest admin path + folder name
        self.zcAdminFolder = adminDirName
        self.zcAdminPath = self.zcPath.joinpath(adminDirName)
        # zc tpl path + folder name
        self.zcTplFolder = tplDirName
        self.zcTplPath = self.zcPath.joinpath(
            'includes', 'templates', tplDirName)

        # list all project files
        self.allFiles = self.listAllFiles(self.projPathInstallFolder)

    @staticmethod
    def script_path():
        return Path(__file__).parent.absolute()

    def listAllFiles(self, dirName):

        listOfFile = os.listdir(dirName)
        allFiles = list()

        for entry in listOfFile:
            fullPath = os.path.join(dirName, entry)
            # If entry is a directory then get the list of files in this directory
            if fullPath.split(os.sep)[-1] not in self.PROJ_EXCLUDE_DIR:
                if os.path.isdir(fullPath):
                    allFiles = allFiles + self.listAllFiles(fullPath)
                else:
                    allFiles.append(fullPath)
        return allFiles

    def exportProjectFiles(self):
        fn = 'project_files'
        obj = {"version": self.VERSION}

        paths = [f.split(str(self.projPathInstallFolder) + os.sep)[1]
                 for f in self.listAllFiles(self.projPathInstallFolder)]
        adminFile = []
        catalogFiles = []

        for f in paths:
            if self.PROJ_ADMIN_FOLDER in f and not '[EDIT_MANUALLY]' in f:
                adminFile.append(f)
            else:
                catalogFiles.append(f)

        obj['admin'] = adminFile
        obj['catalog'] = catalogFiles

        with open(self.script_path().joinpath(fn + '.json'), 'w', encoding='utf-8') as fp:
            json.dump(obj, fp, ensure_ascii=False, indent=4)
        print(
            f"-> Project files in {str(self.script_path().joinpath(fn + '.json'))}")

    def listAdminFiles(self):

        s = "{}{}[RENAME_TO_YOUR_ADMIN_FOLDER]".format(
            self.PROJ_INSTALLATION_FOLDER, os.sep)

        self.adminFiles = [
            f for f in self.allFiles if s in f]

        return self.adminFiles

    def listCatalogFiles(self):

        self.catalogFiles = [
            f for f in self.allFiles if self.PROJ_INSTALLATION_FOLDER + '/includes' in f]

        return self.catalogFiles

    @staticmethod
    def get_filename(path):
        return path.split(os.sep)[-1]

    @staticmethod
    def createDirs(destFiles, mode):

        for e, f in enumerate(destFiles):
            fn = f.split(os.sep)[-1]
            file_name = f[1:].split(os.sep)
            file_name.pop()

            try:
                os.makedirs(os.sep + os.sep.join(file_name),
                            0o755, exist_ok=True)
            except:
                raise ValueError

    def linkFiles(self, mode):

        files = self.listAdminFiles()
        # add admin files to destination files
        destFiles = [r.replace(str(self.projPathAdminFiles), str(self.zcAdminPath))
                     for r in files]

        catalogFiles = self.listCatalogFiles()

        s = str(self.PROJ_INSTALLATION_FOLDER)
        # add catalog files to destination files
        for cf in catalogFiles:
            files.append(cf)
            cf = str(self.zcPath) + cf.split(s)[1]
            if '[YOUR-TEMPLATE]' not in cf:
                destFiles.append(cf)
            else:
                destFiles.append(cf.replace(
                    '[YOUR-TEMPLATE]', self.zcTplFolder))

        # Create Directories
        self.createDirs(destFiles, 'link')

        for s, d in zip(files, destFiles):
            if mode == 'link':
                c = Path(d)
                try:
                    if c.is_file():
                        os.remove(Path(d))
                        print("- " + d)
                    os.symlink(s, d)
                    print("@ " + d)
                except Exception as e:
                    print(e)
                    raise FileNotFoundError
            if mode == 'remove':
                try:
                    os.remove(Path(d))
                    print("- " + d)
                except Exception as e:
                    print(e)

            if mode == 'copy':
                try:
                    if Path(d).is_file():
                        os.remove(Path(d))
                        print("- " + d)
                    copyfile(s, d)
                except Exception as e:
                    print(e)


bookx = DevInstallBookx(
    zencart_path,
    zencart_admin_folder,
    zencart_template_folder)

head = f"\nProject: {bookx.PROJECT} - v{bookx.VERSION}\n"
sep = ("*" * len(head))
msg = sep + head + sep +\
    '\nOptions:\n[1] - symlink\n[2] - copy\n[3] - remove\n[4] - export project files\n' + \
    '[5] - print admin files\n'

options = ''
# 1 - link
# 2 - copy
# 3 - remove
# 4 - export project files
# 5 - print admin files
# 6 - print catalog files

while not options:
    print(msg)
    options = int(input('Option:>'))

if options == 1:
    bookx.linkFiles('link')
if options == 2:
    bookx.linkFiles('copy')
if options == 3:
    bookx.linkFiles('remove')
if options == 4:
    bookx.exportProjectFiles()
# if options == 5:
#    t = bookx.listAdminFiles()
