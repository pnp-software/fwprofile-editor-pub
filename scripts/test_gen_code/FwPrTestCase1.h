/**                                          
 * The functions declared in this file allow the user to control the operation
 * of the FW Profile procedure TestCase1.The following operations can be
 * performed on the procedure: (a) Start, stop and execute the procedure (b)
 * Query the procedure for its start/stop state and for its current node  
 *                                           
 * @note This file was generated on  2023-10-03 19:53:02.707728
 * @author Automatically generated by FW Profile Code Generator
 * @copyright P&P Software GmbH
 */                                          
#ifndef FWPRTESTCASE1_H_
#define FWPRTESTCASE1_H_

/** Enumerated type for the procedure nodes */
typedef enum {
    eTestCase1Stopped = 0;
    eTestCase1N1 = 1;
    eTestCase1N2 = 2;
    eTestCase1N3 = 3;
    eTestCase1N4 = 4;
    eTestCase1N5 = 5;
} eTestCase1Nodes_t;

/** Function to start procedure TestCase1 */
void FwPrTestCase1Start();

/** Function to stop procedure TestCase1 */
void FwPrTestCase1Stop();

/** Function to execute procedure TestCase1 */
void FwPrTestCase1Exec();

/**
 * Check the current state of procedure TestCase1
 * @return 0 if the procedure is not started; 1 otherwise
 */
unsigned int FwPrTestCase1IsStarted();

/**
 * Get the current node of the procedure TestCase1
 * @return -1 if the procedure is stopped; otherwise the current node
 */
eTestCase1Nodes_tTestCase1GetCurNode();

#endif /* FWPRTESTCASE1_H_ */
