""" Convenience functions to support the code generator. """

__author__ = 'Alessandro Pasetti, P&P software GmbH'

import sys
import os
import argparse
import shutil
import csv
import pdb
import json
import operator
import re
import copy
import zipfile
import datetime;

from Format import pattern_edit, markdown_to_doxygen


""" Maximum length of a line in doxygen comment """
MAX_LINE_LENGTH = 80

#===============================================================================
def writeDoxy(lines):
    """ Write a list of strings as a doxygen comment to a string and return the string.
        Empty items in the list of strings are ignored.
        Strings which are longer than MAX_LINE_LENGTH are split to fit within MAX_LINE_LENGTH.    
    """
    f = ''
    newLines = []
    for line in lines:
        if line == '':
            continue
        if len(line)<=MAX_LINE_LENGTH:
            newLines.append(line)
        else:
            words = line.split()
            newLine = ''
            length = 0
            for word in words:
                length += len(word)+1
                newLine += word + ' '
                if (length>MAX_LINE_LENGTH):
                    newLines.append(newLine)
                    newLine = ''
                    length =0
            if length>0:
                newLines.append(newLine)
       
    if len(newLines) == 1:
        f += '/** ' + newLines[0] + ' */'+'\n'
    else:
        f = f + '/**\n'
        for s in newLines:
            f = f + ' * ' + s + '\n'
        f = f + ' */\n'
    return f


#===============================================================================
# Create a body file with the given name and the given content.
def createBodyFile(dirName, fileName, content, shortDesc):
    name = dirName + '/' + fileName
    ct = str(datetime.datetime.now())
    ifdefName = fileName[:-2].replace('_','').upper()
    with open(name, 'w') as fd:
        fd.write('/**                                          \n')
        fd.write(' * @ingroup gen_cfw                          \n')
        fd.write(' *                                           \n')
        fd.write(' * ' + shortDesc + ' \n')
        fd.write(' * This file is part of the PUS Extension of the  CORDET Framework \n')
        fd.write(' *                                           \n')
        fd.write(' * @note This file was generated on  ' + ct + '\n')
        fd.write(' * @author Automatically generated by CORDET Editor Generator\n')
        fd.write(' * @copyright P&P Software GmbH\n')
        fd.write(' */                                          \n')
        fd.write('\n')
        fd.write(content)

#===============================================================================
# Create a header file with the given name and the given content.
def createHeaderFile(dirName, fileName, content, shortDesc):
    name = dirName + '/' + fileName
    ct = str(datetime.datetime.now())
    ifdefName = fileName[:-2].replace('_','').upper()
    with open(name, 'w') as fd:
        fd.write('/**                                          \n')
        fd.write(' * @ingroup gen_fw                           \n')
        fd.write(' *                                           \n')
        fd.write(' * ' + shortDesc + ' \n')
        fd.write(' * This file is part of the PUS Extension of the  CORDET Framework \n')
        fd.write(' *                                           \n')
        fd.write(' * @note This file was generated on  ' + ct + '\n')
        fd.write(' * @author Automatically generated by FW Profile Editor Generator\n')
        fd.write(' * @copyright P&P Software GmbH\n')
        fd.write(' */                                          \n')
        fd.write('#ifndef ' + fileName[:-2].replace('_','').upper() + '_H_\n')
        fd.write('#define ' + fileName[:-2].replace('_','').upper() + '_H_\n')
        fd.write('\n')
        fd.write(content)
        fd.write('#endif /* ' + fileName[:-2].replace('_','').upper() + '_H_ */\n')
    
#===============================================================================
# Create a string representing the #define statement for constant (inclusive of 
# its doxygen comment)
def createVarDef(specItem):
    assert specItem['cat'] == 'DataItem'
    s = ''
    if specItem['remarks'] != '':
        writeDoxy(s, [specItem['desc'], specItem['remarks']])
    else:
        writeDoxy(s, [specItem['desc']])
    s = s + '#define ' + specItem['name'] + '(' +  specItem['value']  + ')'
    return s 
    
        