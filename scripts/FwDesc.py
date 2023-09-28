"""Functions to get the descriptor of a Procedure or State Machine from its json file

The key functions in this module are: getFwPrDesc and getFwSmDesc. They take as
argument the json file of a FW Profile Procedure or State Machine and return
a dictionary holding a complete description of the procedue or state machine.
"""

__author__ = 'Alessandro Pasetti, P&P software GmbH'

import json

# Regular expression for a wait time expressed as an integer number of cycles
reWaitCycles = re.compile("([Ww]ait\s)([A-Za-z0-9_]+)(\s[Cc]ycle)(s*)")

def get_pr_desc(json_obj):
    """ 
    Return a dictionary describing the procedure in the argument josn object
    If the json object does not contain a procedure, None is returned.
    """    
    globals_data = json_obj['globals']['fwprop']
    if globals_data['editorType'] != 'Pr':
        return None
    pr_name = globals_data.get('smName', 'Unnamed Procedure')
    
    # Extract states, notes, and notedots
    states = {}
    notes = []
    notedots = {}
    for item in json_obj.get('states', []):
        item_id = item['id']
        item_type = item['fwprop']['type']
        item_name = item['fwprop'].get('identifier', None)
        if item_type in ('init', 'final'):  # final or final pseuod-node
            item_name = item_type.capitalize()
        
        if item_type == "note":
            notes.append({
                'id': item_id,
                'x': item['attrs']['x'],
                'y': item['attrs']['y'],
                'width': item['attrs']['width'],
                'height': item['attrs']['height'],
                'description': item['fwprop'].get('note', ''),
                'to_states': []  # List to hold states to which the note is attached
            })
        elif item_type == "notedot":
            notedots[item_id] = {
                'x': item['attrs']['x'],
                'y': item['attrs']['y']
            }
        else:   # The state is one of: IPN, FPN, Action Node, or Decision Node
            # If item has a human-readable name, use it as the key
            key = item_name if item_name else item_id
            description = item['fwprop'].get('entryDesc', '')
            is_do_nothing = (description.lower().strip() == 'do nothing')
            states[key] = {
                'id': item_id,
                'name': key,
                'type': item_type,
                'x': item['attrs']['x'],
                'y': item['attrs']['y'],
                'width': item['attrs']['width'],
                'height': item['attrs']['height'],
                'description': description,
                'is_do_nothing': is_do_nothing,
                'outgoing_connections': [],  # List to hold outgoing connections
                'to_notes': []  # List to hold notes attached to the state
            }
            states_by_id[item_id] = key
    
    # Check if a notedot is attached to a state
    for notedot_id, notedot in notedots.items():
        for key, state in states.items():
            if (notedot['x'] > state['x'] and 
                notedot['x'] < state['x'] + state['width'] and
                notedot['y'] > state['y'] and 
                notedot['y'] < state['y'] + state['height']):
                state['to_notes'].append(notedot_id)
    
    # Extract connections
    connections = []
    for connection in json_obj.get('connections', []):
        is_else_guard = (connection['fwprop']['guardCode'].lower().strip() == 'else')
        from_state_key = states_by_id[connection['stateFromID']]
        to_state_key = states_by_id[connection['stateToID']]
        conn_data = {
            'from': states[from_state_key],
            'to': states[to_state_key],
            'guardDesc': connection['fwprop'].get('guardDesc', ''),
            'order': int(connection['fwprop']['order']),
            'is_else_guard': is_else_guard
        }
        connections.append(conn_data)
        
        # Map each connection to its source state in the states dictionary
        for key, state in states.items():
            if state['id'] == conn_data['from']:
                state['outgoing_connections'].append(conn_data)
        
        # Check if the connection is between a note and a notedot and update the note's 'to_states'
        for note in notes:
            if note['id'] == conn_data['from'] and conn_data['to'] in notedots:
                note['to_states'].append(conn_data['to'])
    
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
    """ Dummy main to be used to test functions defined in module """.
    json_file_name = argv[0]
    with open(json_file_name) as fd:
        json_obj = json.load(fd)
        pr_desc = get_pr_desc(jsonFileName, json_obj)
        import pdb; pdb.set_trace()         
    return

if __name__ == "__main__":
    main(sys.argv[1:])

