#!/usr/bin/python3

""" 
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *   
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *   
*   Author: Torvis Wesley
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *   
*   Date:   07/4/2022
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *   
*   Desctiption:    This module is used to convert PDF documents to a
*   text string using Google tesseract.  Created for use with the
*   WordPress custom plugin PDM Document Manager
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *   
*   Version:    1.0.0
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

"""

import platform, sys, json
from re import S
from os.path import exists
from tempfile import TemporaryDirectory
from pathlib import Path
from tokenize import PlainToken
import pytesseract
from pdf2image import convert_from_path
from PIL import Image

# import find document properties script
#import getDocumentProperties as dp

def cleanText(strTextBlob: str):
    strEncode = strTextBlob.encode("ascii", "ignore")
    strDecode = strEncode.decode()
    return strDecode

def extractText(strPDF_File, strTesseractPath, int_psm = "11"):
    if not exists(strPDF_File):
        print("File: " + strPDF_File.name + " does not exist.  Please check the path and/or filename and try agian.")
        return 
    """
        Let's add the configuration option tihe method will use
    """
    myconfig = r"--psm " + int_psm + " --oem 3"

    # Store all the pages of the PDF in a variable
    image_file_list = []

    ''' Main execution point of the program '''
    with TemporaryDirectory() as tempdir:
        # Create a temporary directory to hold our temporary images

        """
        Part #1 : CONVERTING PDF to images:
        """

        if platform.system() == "Windows":
            pdf_pages = convert_from_path(
                strPDF_File, thread_count=4,use_pdftocairo=True, dpi=300, single_file=True
            )
        else:
            # this is the same as above, just a place holder for now
            pdf_pages = convert_from_path(
                strPDF_File, dpi=300, thread_count=4,use_pdftocairo=True, single_file=True
            )

        # Iterate though all the pages stored above
        for page_enumeration, page in enumerate(pdf_pages, start=1):
            # enumerate() 'counts' the pages for us
            
            # Create a file name to store the image
            filename = f"{tempdir}\page_{page_enumeration:03}.jpg"

            # Declaring filename for each page of the PDF as JPG
            # For each page, filename will be:
            # PDF page 1 -> page_001.jpg ...

            page.save(filename, "JPEG")
            image_file_list.append(filename)

        # Part 2 - Recognizing text from the images user OCR

        #with open(strOutputDir, "a") as output_file:
            # Open the file append mode so that
            # All contents of all images are aded to the same file

        # Iterate from 1 to total number of pages
        for image_file in image_file_list:

            # Set filename to reconize text from
            # these files will be:
            # page_1.jpg, page_2.jpg, ect..

            text = str(((pytesseract.image_to_string(Image.open(image_file), config=myconfig))))

            # The recognized text is stored in variable text
            # Any string processing may be applied on text
            # Here, basic formatting has been doclsne:
            # In many PDFs, at line ending, if a word can;t 
            # be written fully, a 'hyphen' is added.
            # The rest of the word is written in teh next line
            # Eg: This is a sample text this word here...
            text = cleanText(text.replace("\n", " "))
            
            # Finally, write the processed text to the stdout console or 
            # append to the list
            #sys.stdout.write(text)
            extractedData.append(text)

        # remove any blank elements of the list
        extractedData = [i for i in extractedData if i]
        # return the data 
        return extractedData

def main(strPDF_File, Path_Tesseract, int_psm = "6"):

   # extract text from file
    return extractText(strPDF_File, Path_Tesseract, int_psm)

 # We only want to run this if it's directly executed!
if __name__ == "__main__":

    try:
     
        if(sys.argv[1]):
            # Path of the Input PDF 

            if platform.system() == "Windows":
                # default tesseract path
                strTess_default_path = r"C:\Program Files\Tesseract-OCR\tesseract.exe"
            else:
                strTess_default_path = ""
            
            str_PDFFile = Path(sys.argv[1])
            
            # assign the psm value to the local variable
            str_psm = sys.argv[3] if len(sys.argv[3]) > 0 else False
            
          # assign the tesseract path value to the local variable
            str_Path_Tesseract = sys.argv[2] if len(sys.argv[2]) > 0 else strTess_default_path

            strText = main(str_PDFFile, str_Path_Tesseract, str_psm)
            print(strText[0])

    except IndexError:
        raise SystemExit(f"Usage: {sys.argv[0]} <path to pdf file i.e c:\\users\\test.pdf> psm a number \
                        between 1 and 13 (see tesseract docs psm modes <11>")
    
