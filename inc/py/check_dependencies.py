#!/usr/bin/python3

""" 
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *   
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *   
*   Author: Torvis Wesley
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *   
*   Date:   07/24/2022
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *   
*   Desctiption:    This module is used to check if required 
*   dependencies are installed.  
*   
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *   
*   Version:    1.0.1
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

"""
import platform, json
import pkg_resources

"""
@returns dictionary list of installed dependencies
"""
def check_dependencies():
    # setup dictionary
    installed_libs = {"installed_packages":[]}

    for package in ['pytesseract', 'pdf2image', 'pdfminer.six']:
        try:
            dist = pkg_resources.get_distribution(package)
            # when package is found add it to the dictionary
            installed_libs["installed_packages"] += [{dist.key:dist.version}]
        except pkg_resources.DistributionNotFound:
             installed_libs["installed_packages"] += [{package:'Not Installed'}]
    # add the sys version to the list of dependencies
    sys_version = platform.system() + " : " + platform.version()
    installed_libs["installed_packages"] += [{"platform": sys_version}]
    installed_libs_json = json.dumps(installed_libs)
    return( installed_libs_json )

# We only want to run this if it's directly executed!
if __name__ == "__main__":
    print(check_dependencies())
