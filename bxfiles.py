#!/usr/bin/python3

'''
by @mesnitu
This file os for development purposes. Test it first in some path.
Edit the edit_destination.json and run this file.
It creates symlinks, removes or copies files.
It also generates a list of all project files that are use on install checks.
'''

import os
import sys
from shutil import copy2 as copyfile
from pathlib import Path
import json
from datetime import datetime


def script_path():
    return Path(__file__).parent.absolute()


def _color(s, c):
    '''add some colors [source, color]'''
    if c == 'yellow':
        s = (f"\033[93m{s}\033[00m")
    if c == 'red':
        s = (f"\033[91m {s}\033[00m")
    if c == 'green':
        s = (f"\033[92m{s}\033[00m")
    if c == 'cyan':
        s = (f"\033[96m{s}\033[00m")
    return s


class DevInstallBookx:

    '''
    Links, copy, removes and list project files to the zencart folder\n
    destination directory, admin folder name, tpl name
    '''

    PROJECT = 'Bookx'

    VERSION = '1.0.1'

    PROJ_EXCLUDE_DIR = ('.git', 'test', '.directory', 'package.json')

    PROJ_INSTALLATION_FOLDER = 'ZC_INSTALLATION'

    PROJ_ADMIN_FOLDER = '[RENAME_TO_YOUR_ADMIN_FOLDER]'

    PROJ_VENDOR_FOLDER = 'vendors'

    PROJ_FILES_LIST_NAME = 'bookx_files.json'

    PROJ_FILES_LIST_DEST = 'includes/extra_datafiles/bookx'

    adminFiles = []

    catalogFiles = []

    def __init__(self, destDir, adminDirName, tplDirName):

        self.projPath = script_path()
        # project files path
        self.projPathInstallFolder = self.projPath.joinpath(
            self.PROJ_INSTALLATION_FOLDER)
        self.projPathAdminFiles = self.projPathInstallFolder.joinpath(
            self.PROJ_ADMIN_FOLDER)

        # zc dest path
        self.zcPath = Path(destDir)
        # zc admin folder name + [dest path]
        self.zcAdminFolderName = adminDirName
        self.zcAdminPath = self.zcPath.joinpath(adminDirName)
        # zc tpl folderpath + folder name
        self.zcTplFolderName = tplDirName
        self.zcTplPath = self.zcPath.joinpath(
            'includes', 'templates', tplDirName)

        # list all project files
        self.allFiles = self.listAllFiles(self.projPathInstallFolder)

    @staticmethod
    def createDirs(destFiles):

        for e, f in enumerate(destFiles):
            fn = f.split(os.sep)[-1]
            file_name = f[1:].split(os.sep)
            file_name.pop()
            try:
                os.makedirs(os.sep + os.sep.join(file_name),
                            0o755, exist_ok=True)
            except:
                raise IsADirectoryError

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

    def createListObj(self, filespath):

        # Creates a obj with all the project files. If online True, the paths are different
        obj = {"version": self.VERSION}

        if self.mode == 'dev':
            obj['admin_files'] = [
                f for f in filespath if self.PROJ_ADMIN_FOLDER in f and not '[EDIT_MANUALLY]' in f]
            obj['catalog_files'] = [
                f for f in filespath if self.PROJ_ADMIN_FOLDER not in f and not '[EDIT_MANUALLY]' in f]
        else:
            pass
            # not in use, see comments bellow
            obj['admin_files'] = [
                f.split(str(self.zcPath))[1].replace(self.zcAdminFolderName, 'admin') for f in filespath if
                self.zcAdminFolderName in f and not '[EDIT_MANUALLY]' in f]
            obj['catalog_files'] = [
                f.split(str(self.zcPath))[1] for f in filespath if self.zcAdminFolderName not in f and not '[EDIT_MANUALLY]' in f]

        # add override files
        overrideFiles = self.listOverrideFiles()
        obj['edit_manually'] = [
            f.replace(str(self.projPathInstallFolder), '')[1:] for f in overrideFiles]
        obj["total_files"] = len(self.allFiles)
        obj["updated"] = datetime.today().strftime("%Y-%m-%d-%H:%M:%S")
        return obj

    def exportProjectFiles(self, filespath=None, mode=None):
        ''' filespath: default None , mode: [default=None, dev]\n
        filespath None to generate dev files'''

        if mode == 'dev':
            self.mode = 'dev'
            filespath = [f.split(str(self.projPathInstallFolder) + os.sep)[1]
                         for f in self.listAllFiles(self.projPathInstallFolder)]
            obj = self.createListObj(filespath)
            project_file = str(script_path().joinpath(
                'dev_' + self.PROJ_FILES_LIST_NAME))
            self.saveFileList(obj, project_file)
            # saving the file again, check comment bellow
            project_file = self.projPathAdminFiles.joinpath(
                'includes', 'extra_datafiles', 'bookx', self.PROJ_FILES_LIST_NAME)
            self.saveFileList(obj, project_file)

        else:
            ''' this is not doing nothing for now, since if a user doesn't use this python file
             it doesn't make sense to have the template_name replaced in the generated file.
             Leaving it here, because on a vps this file could actually install all this stuff.'''
            # obj = self.createListObj(filespath)
            # project_file = self.projPathAdminFiles.joinpath(
            #    'includes', 'extra_datafiles', 'bookx', self.PROJ_FILES_LIST_NAME)
            pass
        # self.saveFileList(obj, project_file)

        print(_color("Project files in " + str(project_file), 'green'))

    @staticmethod
    def saveFileList(files, destfile):

        with open(destfile, 'w', encoding='utf-8') as fp:
            json.dump(files, fp, ensure_ascii=False, indent=4)

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

    def listOverrideFiles(self):

        self.overrideFiles = [
            f for f in self.allFiles if '[EDIT_MANUALLY]' in f]
        return self.overrideFiles

    @staticmethod
    def _remove(f):
        os.remove(Path(f))
        print(_color("-", 'red') + f)

    def processFiles(self, mode, online=None):
        ''' mode: [link, copy, remove] '''

        files = self.listAdminFiles()
        self.mode = mode
        # add admin files to destination files
        destFiles = [r.replace(str(self.projPathAdminFiles), str(self.zcAdminPath))
                     for r in files]

        catalogFiles = self.listCatalogFiles()

        s = str(self.PROJ_INSTALLATION_FOLDER)
        # add catalog files to destination files
        for f in catalogFiles:
            files.append(f)
            f = str(self.zcPath) + f.split(s)[1]
            if '[YOUR-TEMPLATE]' not in f:
                destFiles.append(f)
            else:
                destFiles.append(f.replace(
                    '[YOUR-TEMPLATE]', self.zcTplFolderName))

        # Call export files in link or copy mode
        # For now it's only exporting in dev mode (no path replacement)
        if mode == 'link' or mode == 'copy':
            self.exportProjectFiles(None, "dev")

        # Create Directories
        self.createDirs(destFiles)
        # (s)ource, (d)est
        for s, d in zip(files, destFiles):

            if mode == 'link':
                try:
                    if Path(d).is_file():
                        self._remove(d)
                    os.symlink(s, d)
                    print(_color("@", 'green') + d)
                except Exception as e:
                    print(e)
                    raise FileNotFoundError

            if mode == 'remove':
                try:
                    self._remove(d)
                except Exception as e:
                    print(e)

            if mode == 'copy':
                try:
                    if Path(d).is_file():
                        self._remove(d)
                    copyfile(s, d)
                    print(_color("+ ", 'yellow') + d)
                except Exception as e:
                    print(e)
            if mode == 'copy_from':
                # copies files from destination to source (caution)
                try:
                    if Path(d).is_file():
                        # copyfile(d, s)
                        pass
                    print(_color("<- copy from", 'yellow') + d)
                except Exception as e:
                    print(e)


def load_json():
    with open(script_path().joinpath('edit_destination.json'), 'r', encoding="utf-8") as fp:
        return json.load(fp)


def check_destinations():
    try:
        rf = load_json()

        if rf['dest'] == '' or rf['admin_folder_name'] == '' \
                or rf['template_folder_name'] == '':
            raise ImportError
        # test destinations
        t = Path(rf['dest']).joinpath(rf['admin_folder_name'])
        if not t.is_dir():
            raise FileNotFoundError

        t = Path(rf['dest']).joinpath('includes', 'templates',
                                      rf['template_folder_name'])
        if not t.is_dir():
            raise FileNotFoundError
        # Check for Vendor and try to install via composer command
        vendor = script_path().joinpath(
            "ZC_INSTALLATION", "includes", "classes", "vendors")
        if not vendor.is_dir():
            try:
                s = install_vendors()
                print(s)
            except:
                raise OSError

    except FileNotFoundError:
        print(_color(
            f"\n\n!!! Check your destination folder: {t.name} folder not Found !!!\n\n", 'red'))
        exit()
    except ImportError:
        print(_color("\n\nNo destinations found in set_destination.json.\n\n", 'red'))
        exit()
    except OSError:
        print(_color(
            f"\n!!! Coud not install vendor with composer and {t.name} folder was not Found !!!\n", 'yellow'))

    return rf


def cls():
    os.system('cls' if os.name == 'nt' else 'clear')


def install_vendors():
    try:
        os.system('cls' if os.name == 'nt' else 'clear')
        print(_color("Bookx uses 2 vendor packages." +
                     "Trying to install vendor packages with composer." +
                     "", 'green'))
        if os.system('composer update') > 0:
            raise ImportWarning

    except ImportWarning as err:
        print(_color(
            "Could not install vendors. Check the composer file, or download from: ", 'red'))
        print(_color("https://github.com/gumlet/php-image-resize.git", 'cyan'))
        print(_color("https://github.com/erusev/parsedown.git", 'cyan'))
        return err


def main():

   # check if set-destination file as values and if the directories exists
    rf = check_destinations()
    # intiate instance
    bookx = DevInstallBookx(
        rf['dest'],
        rf['admin_folder_name'],
        rf['template_folder_name'])

    head = _color(f"\nProject: {bookx.PROJECT} - v{bookx.VERSION}\n", 'cyan')
    sep = ("*" * len(head)*2)
    msg = sep + head + sep
    msg += "\n" + _color("Destination Files:", 'green') + "\n"
    msg += "\n".join(["- {}: {}".format(_color(k, 'yellow'), _color(v, 'cyan'))
                      for k, v in rf.items() if "description" not in k])
    msg += '\n\n'+_color("Options:", 'green') + '\n'
    opt = [
        "[1] - symlink",
        "[2] - copy",
        "[3] - remove",
        "[4] - export project files",
        "[5] - copy_from(dev)",
        "[6] - install vendors(dev, uses composer)",
        "[0] - Quit"]
    msg += "\n".join(opt)

    options = False

    while not options:
        print(msg)
        options = input('Option:>')

    if options == '0':
        exit()
    if options == '1':
        bookx.processFiles('link')
    if options == '2':
        bookx.processFiles('copy')
    if options == '3':
        bookx.processFiles('remove')
    if options == '4':
        bookx.exportProjectFiles(None, 'dev')
    if options == '5':
        bookx.processFiles('copy_from')
    if options == '6':
        install_vendors()


if __name__ == "__main__":
    main()
