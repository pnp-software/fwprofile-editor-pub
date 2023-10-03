/**                                          
 * @ingroup gen_cfw                          
 *                                           
 * Body file for module implementing procedure TestCase1  
 *                                           
 * @note This file was generated on  2023-10-03 19:53:02.707794
 * @author Automatically generated by CORDET Editor Code Generator
 * @copyright P&P Software GmbH
 */                                          

#include <TestCase1.h>

/** The current procedure node */
static eTestCase1Nodes_t curNode = FwPrTestCase1Stopped;

unsigned int FwPrTestCase1IsStarted() {
    return curNode != eTestCase1Stopped;
}

eTestCase1Nodes_t FwPrTestCase1GetCurNode() {
    return curNode;
}

void FwPrTestCase1Start() {
    if (curNode != eTestCase1Stopped)
        return;
    curNode = eTestCase1Init;
}

void FwPrTestCase1Stop() {
    if (curNode == eTestCase1Stopped)
        return;
    curNode = eTestCase1Stopped;
}

void FwPrTestCase1Execute() {
    if (curNode == eTestCase1Stopped)
        return;
    while (1) {
        if (curNode == eTestCase1Init) {
            curNode = eTestCase1N1;
            FwPrTestCase1N1();
            if (FwPrTestCase1Decision1N2() == 1) {
                curNode = eTestCase1N2;
                FwPrTestCase1N2();
                curNode = eTestCase1N4;
                FwPrTestCase1N4();
                curNode = eTestCase1Stopped;
                return;
            } else if (FwPrTestCase1Decision1Final() == 1) {
                curNode = eTestCase1Stopped;
                return;
            } else  {
                FwPrTestCase1N3();
            }
        }
        if (curNode == eTestCase1N3) {
            if (FwPrTestCase1N3N5() == 0) {
                return
            curNode = eTestCase1N5;
            FwPrTestCase1N5();
            curNode = eTestCase1Stopped;
            return;
            }
        }
    }
}
