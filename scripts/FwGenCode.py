""" Script to generate the implementation of a FW Profile Procedure.
This script parses the json representation of a procedure and generates
the C code implementing it. The json representation is the one created
withthe FW Profile Editor.

The generator script is called as follows:

> python FwGenCode.py FwModel.json CodeDirPath
    
'FwModel.json' is the json representation of the procedure and 'CodeDirPath'
is the path to the directory where the C code is generated. 

The structure of the generated code can be controlled through the 
configuration parameters defined at the beginning of this module.


The following C files are generated for a json model:

- <Prefix>FwModel.h: header file declaring the functons through which the
                     procedure is controlled
- <Prefix>FwModel.c: body file implementing the functions through which 
                     the procedure is controlled
- <Prefix>FwModel<Suffix>.h: header file declaring the functions, which 
                             implement the procedure actions and guards
- <Prefix>FwModel<Suffix>.c: dummy implementation of the functions, which 
                             implement the procedure actions and guards 
                            
The <prefix> and <suffix> are strings defined through configuration parameters.

The user should normally provide an implementation of the header file
<Prefix>FwModel<Suffix>.h. The body file <Prefix>FwModel<Suffix>.c can
be used as a template or as a test program for the generated code.

The generator script is designed to generate an implementation of a 
procedure which privileges speed of execution over code memory usage.
"""

__author__ = 'Alessandro Pasetti, P&P software GmbH'

import json
import sys
import pdb

from Utilities import createHeaderFile, createBodyFile, writeDoxy
from FwDesc import get_pr_desc

""" Prefix for file names for files implementing procedures """
fn_pr_prefix = 'FwPr'
""" Suffix for the header file name declaring the procedure user functions """
uh_pr_suffix = 'User'
""" Prefix for enumerator holding procedure nodes """
enum_pr_prefix = 'e'
""" Prefix for function names for procedure files """
fnc_pr_prefix = 'FwPr'
""" Size of indentation jump in generated C-code """
d_ind = 4*' '
""" If True, then no procedure counters are not generated """
no_cnt = True

def get_guard_fnc(pr_desc, connection):
    """ Return the name of the function implementing the guard on the connection
        or None if the connection has no guard or the else guard """
    pr_name = pr_desc['name']
    states = pr_desc['states']
    states_by_id = pr_desc['states_by_id']
    from_state = states_by_id[connection['from']]
    to_state = states_by_id[connection['to']]
    assert(from_state['type'] in ('init', 'state', 'choice'))
    assert(to_state['type'] in ('final', 'state', 'choice'))
    if connection['guardDesc'] == '' or connection['is_else_guard']:
        return None
    return fnc_pr_prefix+pr_name+from_state['name']+to_state['name']
    
    
def get_node_name(pr_desc, state):
    """ Return the name of the enumerator holding the name of the node
        represented by the argument state
    """
    assert(state['type'] in ('init', 'final', 'state'))
    pr_name = pr_desc['name']
    return enum_pr_prefix+pr_name+state['name']

    
def get_node_fnc(pr_desc, state):
    """ Return the name of the function implementing the action of the node
        represented by the argument state
    """
    assert(state['type'] in ('state'))
    pr_name = pr_desc['name']
    return fnc_pr_prefix+pr_name+state['name']
    
    
def pr_create_header(pr_desc, dir_path):
    """ Create the header file for the procedure module.
        The header file declares the functions to start, stop and execute
        the procedure; the functions to get the current node, the
        two procedure counters; and the enumerated type holding the node
        identifiers.
        The first argument is the descriptor of the procedure returned
        by function get_pr_desc. The second argument is the fully qualified
        name of the directory where the file is generated.
    """
    pr_name = pr_desc['name']
    
    # Define the enumerated type holding the node identifiers
    s = ''
    i = 0
    s += writeDoxy(['Enumerated type for the procedure nodes'])
    s += 'typedef enum {\n'
    s += d_ind+enum_pr_prefix+pr_name+'Stopped'+' = 0;\n'
    for state_name,state in pr_desc['states'].items():
        if state['type'] == 'state':
            i = i+1
            s += d_ind+enum_pr_prefix+pr_name+state_name+' = '+str(i)+';\n'
    s+= '} '+enum_pr_prefix+pr_name+'Nodes_t;\n\n'
  
    s += writeDoxy(['Function to start procedure '+pr_name])
    s += 'void '+fnc_pr_prefix+pr_name+'Start();\n\n'
    s += writeDoxy(['Function to stop procedure '+pr_name])
    s += 'void '+fnc_pr_prefix+pr_name+'Stop();\n\n'
    s += writeDoxy(['Function to execute procedure '+pr_name])
    s += 'void '+fnc_pr_prefix+pr_name+'Exec();\n\n'
    s += writeDoxy(['Check the current state of procedure '+pr_name,
                   '@return 0 if the procedure is not started; 1 otherwise'])
    s += 'unsigned int '+fnc_pr_prefix+pr_name+'IsStarted();\n\n'
    s += writeDoxy(['Get the current node of the procedure '+pr_name,
                   '@return -1 if the procedure is stopped; otherwise the current node'])
    s += enum_pr_prefix+pr_name+'Nodes_t'+pr_name+'GetCurNode();\n\n'
    if not no_cnt:
        s += writeDoxy(['Get the procedure execution coounter for procedure '+pr_name, \
                       '@return the execution counter of the procedure'])
        s += 'unsigned int '+fnc_pr_prefix+pr_name+'GetPrExecCnt();\n\n'
        s += writeDoxy(['Get the node execution counter for procedure '+pr_name, \
                       '@return the execution counter of the procedure'])
        s += 'unsigned int '+fnc_pr_prefix+pr_name+'GetNodeExecCnt();\n\n'
    
    func_desc = 'The functions declared in this file allow the user to  control ' + \
                'the operation of the FW Profile procedure ' + pr_name + '.' + \
                'The following operations can be performed on the procedure: ' + \
                '(a) Start, stop and execute the procedure \n' + \
                '(b) Query the procedure for its start/stop state and for its current node\n'
    if not no_cnt:
        func_desc = func_desc + \
                '(c) Get the current value of procedure and node execution counters '
    createHeaderFile(dir_path, fn_pr_prefix+pr_name, s, func_desc)
    
    
def pr_create_user_header(pr_desc, dir_path):
    """ Create the header file which declares the functions implementing
        the node actions and the guards.
    """
    s = ''
    i = 0
    pr_name = pr_desc['name']
    for state_name,state in pr_desc['states'].items():
        i = i+1
        if state['type'] == 'state' and not state['is_do_nothing']:
            notes = []
            for note in state['to_notes']:
                notes.append('')
                notes.append(note['description'])
            s += writeDoxy(['Function implementing the action for node '+state_name, \
                            state['description']] + notes)
            s += 'void '+fnc_pr_prefix+pr_name+state_name+'();\n\n'

    i = 0
    for connection in pr_desc['connections']:
        i = i+1
        if (connection['guardDesc'] != '') and not connection['is_else_guard']:
            src_state_name = pr_desc['states_by_id'][connection['from']]['name']
            dest_state_name = pr_desc['states_by_id'][connection['to']]['name']
            connection_name = pr_name + src_state_name + 'To' + dest_state_name
            s += writeDoxy(['Function implementing the guard from '+src_state_name+\
                            ' to '+dest_state_name, connection['guardDesc']])
            s += 'void '+fnc_pr_prefix+pr_name+connection_name+'();\n\n'

    func_desc =  'The functions in this file implement the actions and ' + \
                 'guards of the FW Profile procedure of ' + pr_name + '.' + \
                 'The user is responsible for providing a C body file which ' + \
                 'implements all the functions declared in this header file.' 
    createHeaderFile(dir_path, fn_pr_prefix + pr_name + uh_pr_suffix, s, func_desc)
    

def pr_create_body(pr_desc, dir_path):
    """ Create the body file for the procedure module.
        The first argument is the descriptor of the procedure returned
        by function get_pr_desc. The second argument is the fully qualified
        name of the directory where the file is generated.
    """        
    pr_name = pr_desc['name']
    states_by_id = pr_desc['states_by_id']
    
    s = '#include <'+pr_name+'.h>\n\n'
    s += writeDoxy(['The current procedure node'])
    s += 'static '+enum_pr_prefix+pr_name+'Nodes_t curNode = FwPr'+pr_name+'Stopped;\n\n'
    if not no_cnt:
        s += writeDoxy(['The procedure execution counter'])
        s += 'static unsigned int prExecCnt = 0;\n\n' 
        s += writeDoxy(['The node execution counter'])
        s += 'static unsigned int nodeExecCnt = 0;\n\n' 
    
    s += 'unsigned int'+' '+fnc_pr_prefix+pr_name+'IsStarted() {\n'
    s += d_ind+'return curNode != '+enum_pr_prefix+pr_name+'Stopped;\n'
    s += '}\n\n'
    
    s += enum_pr_prefix+pr_name+'Nodes_t '+fnc_pr_prefix+pr_name+'GetCurNode() {\n'
    s += d_ind+'return curNode;\n'
    s += '}\n\n'

    if not no_cnt:
        s += 'unsigned int '+fnc_pr_prefix+pr_name+'GetPrExecCnt() {\n'
        s += d_ind+'return prExecCnt;\n'
        s += '}\n\n'

        s += 'unsigned int '+fnc_pr_prefix+pr_name+'GetNodeExecCnt() {\n'
        s += d_ind+'return nodeExecCnt;\n'
        s += '}\n\n'
 
    s += 'void '+fnc_pr_prefix+pr_name+'Start() {\n'
    s += d_ind+'if (curNode != '+enum_pr_prefix+pr_name+'Stopped)\n'
    s += d_ind+d_ind+'return;\n'
    s += d_ind+'curNode = '+enum_pr_prefix+pr_name+'Init;\n'
    if not no_cnt:
        s += d_ind+'prExecCnt = 0;\n'
        s += d_ind+'nodeExecCnt = 0;\n'
    s += '}\n\n'

    s += 'void '+fnc_pr_prefix+pr_name+'Stop() {\n'
    s += d_ind+'if (curNode == '+enum_pr_prefix+pr_name+'Stopped)\n'
    s += d_ind+d_ind+'return;\n'
    s += d_ind+'curNode = '+enum_pr_prefix+pr_name+'Stopped;\n'
    s += '}\n\n'
    
    s += 'void '+fnc_pr_prefix+pr_name+'Execute() {\n'
    s += d_ind+'if (curNode == '+enum_pr_prefix+pr_name+'Stopped)\n'
    s += d_ind+d_ind+'return;\n'
    if not no_cnt:
        s += d_ind+'prExecCnt++;\n'
        s += d_ind+'nodeExecCnt++;\n'
    s += d_ind+'while (1) {\n'
    n_ind = 2

    def is_node_transient(pr_desc, state):
        """ Return True if the argument state represents a transient procedure node.
            A transient node is either a decision node or a final node, or an action  
            node whose out-going connection has either no guard or has an else guard.
            Or, equivalently, a non-transient node is either the initial node or a 
            node with a non-else guard on its out-going connection.
            A transient node is a node where the procedure may pause while waiting to
            be executed.
        """
        assert(state['type'] in ('init', 'final', 'state', 'choice'))
        if state['type'] == 'init':
            return False
        if len(state['outgoing_connections']) != 1:
            return True
        if get_guard_fnc(pr_desc, state['outgoing_connections'][0]) == None:
            return True
        return False
    
    def proc_sub_tree(n_ind, pr_desc, node):
        """ Process the procedure sub-tree starting at 'node'
            The argument node is one of the following: 
            (a) the final node: the execution is declared to have terminated
            (b) a transient action node: the node action is executed and then the
                function is called (recusively) on the next node
            (c) a non-transient action node: the node action is executed
            (d) a decision node: the function is called (recursively) on
                each of the successor nodes
        """
        nonlocal s
        ind = d_ind*n_ind
        if not is_node_transient(pr_desc, node):
            s += ind + get_node_fnc(pr_desc, node)+'();\n'
            return
        if node['type'] == 'final':
            s += ind + 'curNode = '+enum_pr_prefix+pr_name+'Stopped;\n'
            s += ind + 'return;\n'
        if node['type'] == 'state':
            s += ind + 'curNode = ' + get_node_name(pr_desc,node) + ';\n'
            s += ind + get_node_fnc(pr_desc, node)+'();\n'
            next_node = states_by_id[node['outgoing_connections'][0]['to']]
            proc_sub_tree(n_ind, pr_desc, next_node)
        if node['type'] == 'choice':
            sorted_connections = sorted(node['outgoing_connections'], key=lambda x: x['order'])
            for connection in sorted_connections:
                guard_fnc = get_guard_fnc(pr_desc, connection)
                if connection['order'] > 1: 
                    if guard_fnc != None:
                        s += 'if ('+guard_fnc+'() == 1) {\n'
                    else:
                        s += ' {\n'
                else:
                    s += ind + 'if ('+guard_fnc+'() == 1) {\n'
                order = connection['order']
                next_node = states_by_id[sorted_connections[order-1]['to']]
                proc_sub_tree(n_ind+1, pr_desc, next_node)
                if connection['order'] < len(sorted_connections):
                    s += n_ind*d_ind + '} else '
                else:
                    s += n_ind*d_ind + '}\n'
        assert('false')
        
    for state_name, node in pr_desc['states'].items():
        if not is_node_transient(pr_desc, node):
            s += n_ind*d_ind + 'if (curNode == ' + get_node_name(pr_desc,node) + ') {\n' 
            n_ind = n_ind + 1
            guard_fnc = get_guard_fnc(pr_desc, node['outgoing_connections'][0])
            next_node = states_by_id[node['outgoing_connections'][0]['to']]
            if not no_cnt:
                s += n_ind*d_ind + 'nodeExecCnt = 0;\n'
            if guard_fnc != None:
                assert(node['type'] == 'state')
                s += n_ind*d_ind + 'if ('+guard_fnc+'() == 0) {\n' 
                s += (n_ind+1)*d_ind + 'return\n'    
                    
                proc_sub_tree(n_ind, pr_desc, next_node) 
                s += n_ind*d_ind + '}\n'
                n_ind = n_ind - 1
            else:
                assert(node['type'] == 'init')
                proc_sub_tree(n_ind, pr_desc, next_node)
                n_ind = n_ind - 1
            s += n_ind*d_ind + '}\n'
      
    n_ind = n_ind - 1                            
    s += n_ind*d_ind + '}\n'    # While (1)    
    s += '}\n\n'    
    
    short_desc = 'Body file for module implementing procedure '+pr_name
    createBodyFile(dir_path, fn_pr_prefix+pr_name, s, short_desc)


def main(argv):
    """ Dummy main to be used to test functions defined in module """
    jsonFileName = argv[0]
    dir_path = argv[1]
    with open(jsonFileName) as fd:
        json_obj = json.load(fd)
        pr_desc = get_pr_desc(json_obj)
        pr_create_user_header(pr_desc, dir_path)
        pr_create_header(pr_desc, dir_path)
        pr_create_body(pr_desc, dir_path)
    return

if __name__ == "__main__":
    main(sys.argv[1:])

