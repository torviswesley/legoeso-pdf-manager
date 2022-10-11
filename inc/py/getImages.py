#!/usr/bin/python3

""" 
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *   
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *   
*   Author: Torvis Wesley
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *   
*   Date:   07/23/2022
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *   
*   Desctiption:    This module is used to extract images from PDF
*   documents.  Created for use with the
*   WordPress custom plugin PDM Document Manager
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *   
*   Version:    1.0.0
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

"""

import platform, sys
import os
from os.path import exists
from tempfile import TemporaryDirectory
from pathlib import Path
from pdf2image import convert_from_path

def images_from_pdf(strPDF_File:str, strImage_dir:str):

    if not exists(strPDF_File):
        sys.stdout.write("File: " + strPDF_File + " does not exist.  Please check the path and/or filename and try agian.")
        return 

    # create the directory if it does not already exist
    if not exists(strImage_dir):
        os.mkdir(strImage_dir)

   # Store the paths to the extracted images from the PDF in a list
    image_file_list = []

    ''' Main execution point of the program '''
    with TemporaryDirectory() as tempdir:
        # Create a temporary directory to hold our temporary images

        """
        Part #1 : CONVERTING PDF to images:
        """

        if platform.system() == "Windows":
            pdf_pages = convert_from_path(
                strPDF_File, thread_count=3, size=1024, use_pdftocairo=True, dpi=300,
                 single_file=True,
                 last_page=1
            )
        else:
            # this is the same as above, just a place holder for now
            pdf_pages = convert_from_path(
                strPDF_File, size=1024, dpi=300, thread_count=3, use_pdftocairo=True, 
                single_file=True,
                last_page=1
            )

        # Iterate though all the pages stored above
        for page_enumeration, page in enumerate(pdf_pages, start=1):
            # enumerate() 'counts' the pages for us
            
            # Create a file name to store the image
            filename = f"{strImage_dir}\page_{page_enumeration:03}.jpg"

            # Declaring filename for each page of the PDF as JPG
            # For each page, filename will be:
            # PDF page 1 -> page_001.jpg ...

            page.save(filename, "jpeg")
            image_file_list.append(filename)

        # return the data 
        return image_file_list
"""
    Main method, used to call all methods that will be used
"""
def main(strPDF_File, strImageDir):

    # extract text from file
    return images_from_pdf(strPDF_File, strImageDir)

# We only want to run this if it's directly executed!
if __name__ == "__main__":

    blnDebug = False

    if not blnDebug == True:
        try:
        
            if(sys.argv[1]):
                # get the path to the PDF file
                str_PDFFile = Path(sys.argv[1])
                # get the patch to the images directory
                str_image_dir = sys.argv[2] if len(sys.argv[2]) > 0 else False

               


        except IndexError:
            raise SystemExit(f"Usage: {sys.argv[0]} <path to pdf file i.e c:\\users\\test.pdf> <path to image output directory  file i.e c:\\users\\output_images>")
    else:
        # Path of the Input PDF 
        # Debug variables
        str_PDFFile = r"C:\Apache24\apps\demo2/wp-content/uploads/2022/07/726658_2022-05-27_102514.pdf"
        str_image_dir = r"C:\Apache24\apps\demo2/wp-content/uploads/2022/07/pdm_data"
    
    # call the main function and print the output             
    strText = main(str_PDFFile, str_image_dir)
    print(strText[0])
