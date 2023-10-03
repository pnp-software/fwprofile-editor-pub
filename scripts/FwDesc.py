"""Functions to get the descriptor of a Procedure or State Machine from its json file

The key functions in this module are: get_pr_desc and get_sm_desc. They take as
argument the json file of a FW Profile Procedure or State Machine and return
a dictionary holding a complete description of the procedue or state machine.
"""

__author__ = 'Alessandro Pasetti, P&P software GmbH'

import json

def get_pr_desc(json_obj):
    """ 
    Return a dictionary describing the procedure in the argument json object
    If the json object does not contain a procedure, None is returned.
    The procedure desriptor returned by this procedure holds:
    - The procedure name
    - The dictionary of procedure states indexed by their name
    - The dictionary of procedure states indexed by their ID
    - The list of connections in the procedures
    - The list of notes in the procedure
    
    A procedure 'state' is one of: (a) the initial pseudo-state;
    (b) the final pseudo-state, (c) an action node, or (d) a decision node.
    The name of an action nodes or decision nodes is defined in the json model.
    The name of the initial and final pseudo-nodes is set to: 'Initial' and
    'Final'. 
    
    A procedure 'connection' is a connection between two procedure states. 
    
    Notes may be attached to procedure states. Each node dictionary holds a 
    list of states to which the note is attached and each state dictionary
    holds a list of notes to which it is attached.
    
    The 'is_do_nothing' attribute is attached to procedure states which 
    represent an action node whose action consists of the (case-insensitive)
    string 'do nothing'.
    
    The is_else_guard attribute is attached to connections whose guard is
    equal to the (case-insensitive0 string 'else'.
    """    
    globals_data = json_obj['globals']['fwprop']
    if globals_data['editorType'] != 'Pr':
        return None
    pr_name = globals_data.get('smName', 'Unnamed Procedure')
    
    states = {}                 # Dictionary of states indexed by their name
    notes = []                  # List of notes
    notedots = {}               # Dictionary of notedots indexed by ID
    states_by_id = {}           # Dictionary of states indexed by their ID
    notes_by_id = {}            # Dictionary of notes indexed by their ID
    states_by_notedot_id = {}   # Dictionary of states indexed by the ID of the notedot attached to them
    
    for item in json_obj.get('states', []):
        item_id = item['id']
        item_type = item['fwprop']['type']
        item_name = item['fwprop'].get('identifier', None)
        if item_type in ('init', 'final'):  # initial or final pseuod-node
            item_name = item_type.capitalize()
        
        if item_type == "note":
            note = {
                'id': item_id,
                'x': item['attrs']['x'],
                'y': item['attrs']['y'],
                'width': item['attrs']['width'],
                'height': item['attrs']['height'],
                'description': item['fwprop'].get('note', '').replace('\n',' '),
                'to_states': []  # List of states to which the note is attached
            }
            notes.append(note)
            notes_by_id[item_id] = note
        elif item_type == "notedot":
            notedots[item_id] = {
                'id': item_id,
                'x': item['attrs']['x'],
                'y': item['attrs']['y']
            }
        else:   # The state is one of: IPN, FPN, Action Node, or Decision Node
            key = item_name.capitalize() if item_type == 'choice' else item_name
            description = item['fwprop'].get('entryDesc', '').replace('\n',' ')
            is_do_nothing = (description.lower().strip() == 'do nothing')
            state = {
                'id': item_id,
                'name': key,
                'type': item_type,
                'x': item['attrs']['x'],
                'y': item['attrs']['y'],
                'width': item['attrs']['width'],
                'height': item['attrs']['height'],
                'description': description,
                'is_do_nothing': is_do_nothing,
                'outgoing_connections': [],  # List of outgoing connections
                'to_notes': []  # List of notes attached to the state
            }
            states[key] = state
            states_by_id[item_id] = state
    
    # Check if a notedot is attached to a state
    for notedot_id, notedot in notedots.items():
        for key, state in states.items():
            if (notedot['x'] > state['x'] and 
                notedot['x'] < state['x'] + state['width'] and
                notedot['y'] > state['y'] and 
                notedot['y'] < state['y'] + state['height']):
                states_by_notedot_id[notedot['id']] = state
    
    # Extract connections
    connections = []
    for connection in json_obj.get('connections', []):
        guard_desc = connection['fwprop'].get('guardDesc', '').replace('\n',' ')
        is_else_guard = (guard_desc.lower().strip() == 'else')
        conn_data = {
            'from': connection['stateFromID'],
            'to': connection['stateToID'],
            'guardDesc': guard_desc,
            'order': int(connection['fwprop']['order']),
            'is_else_guard': is_else_guard
        }
        connections.append(conn_data)
        
        # Map each connection to its source state in the states dictionary
        for key, state in states.items():
            if state['id'] == conn_data['from']:
                state['outgoing_connections'].append(conn_data)
        
        # Check if the connection is between a note and a notedot attached to a state
        for note in notes:
            if note['id'] == conn_data['from'] and conn_data['to'] in notedots:
                notedot = notedots[conn_data['to']]
                if notedot['id'] in states_by_notedot_id:
                    target_state = states_by_notedot_id[notedot['id']]
                    target_state['to_notes'].append(note)
                    note['to_states'].append(target_state)
    
    # Create the refined dictionary
    desc = {
        'name': pr_name,
        'states': states,
        'states_by_id': states_by_id,
        'connections': connections,
        'notes': notes
    }
    
    return desc


def main(argv):
    """ Dummy main to be used to test functions defined in module """
    json_file_name = argv[0]
    with open(json_file_name) as fd:
        json_obj = json.load(fd)
        pr_desc = get_pr_desc(jsonFileName, json_obj)
        import pdb; pdb.set_trace()         
    return

if __name__ == "__main__":
    main(sys.argv[1:])

