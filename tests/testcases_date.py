testcases = [
    # wrong request method
    {
        "method": "GET",
        "payload": {}, 
        "headers": {}, 
        "expected_output": {
            "errors": [
                {
                "id": 800,
                "htmlcode": 405,
                "message": "Method not supported by the target resource"
                }
            ]
        }
    },

    # correct request method, no data
    {
        "method": "POST",
        "payload": {}, 
        "headers": {}, 
        "expected_output": {
            "errors": [
                {
                "id": 813,
                "htmlcode": 400,
                "message": "'mintime' and/or 'maxtime' are/is not set in the request"
                }
            ]
        }
    },

    # correct request method, no maxtime data
    {
        "method": "POST",
        "payload": {'mintime': '20'}, 
        "headers": {}, 
        "expected_output": {
            "errors": [
                {
                "id": 813,
                "htmlcode": 400,
                "message": "'mintime' and/or 'maxtime' are/is not set in the request"
                }
            ]
        }
    },

    # correct request method, no mintime data
    {
        "method": "POST",
        "payload": {'maxtime': '20'}, 
        "headers": {}, 
        "expected_output": {
            "errors": [
                {
                "id": 813,
                "htmlcode": 400,
                "message": "'mintime' and/or 'maxtime' are/is not set in the request"
                }
            ]
        }
    },

    # correct request method, mintime > maxtime
    {
        "method": "POST",
        "payload": {'maxtime': '20', 'mintime': '50'}, 
        "headers": {}, 
        "expected_output": {
            "errors": [
                {
                "id": 810,
                "htmlcode": 422,
                "message": "'from' date should come before the 'to' date"
                }
            ]
        }
    },

    # correct request method, mintime == maxtime ( != 0 )
    {
        "method": "POST",
        "payload": {'maxtime': '50', 'mintime': '50'}, 
        "headers": {}, 
        "expected_output": {
            "errors": [
                {
                "id": 811,
                "htmlcode": 422,
                "message": "'from' date should be different to the 'to' date (or both zero)"
                }
            ]
        }
    },

    # correct request type, mintime and maxtime (mintime not numeric)
    {
        "method": "POST",
        "payload": {'maxtime': '30', 'mintime': 'test'}, 
        "headers": {}, 
        "expected_output": {
            "errors": [
                {
                "id": 812,
                "htmlcode": 422,
                "message": "'from' date and/or 'to' date should be numeric"
                }
            ]
        }
    },

    # correct request type, mintime and maxtime (maxtime not numeric)
    {
        "method": "POST",
        "payload": {'maxtime': 'test', 'mintime': '30'}, 
        "headers": {}, 
        "expected_output": {
            "errors": [
                {
                "id": 812,
                "htmlcode": 422,
                "message": "'from' date and/or 'to' date should be numeric"
                }
            ]
        }
    },

    # correct request type, mintime and maxtime set correctly (both to zero)
    {
        "method": "POST",
        "payload": {'maxtime': '0', 'mintime': '0'}, 
        "headers": {}, 
        "expected_output": [
            {
                "errors": [
                    {
                        "id": 820,
                        "htmlcode": 500,
                        "message": "Unable to change 'from' date and 'to' date (maybe values are already set?)"
                    }
                ]
            },
            {
                "errors": [],
                "data": {
                    "mintime": 0,
                    "maxtime": 0
                }
            }
        ]
    },

    # correct request type, mintime and maxtime set correctly (not zero)
    {
        "method": "POST",
        "payload": {'maxtime': '100', 'mintime': '40'}, 
        "headers": {}, 
        "expected_output": [
            {
                "errors": [
                    {
                        "id": 820,
                        "htmlcode": 500,
                        "message": "Unable to change 'from' date and 'to' date (maybe values are already set?)"
                    }
                ]
            },
            {
                "errors": [],
                "data": {
                    "mintime": 40,
                    "maxtime": 100
                }
            }
        ]
    },
]