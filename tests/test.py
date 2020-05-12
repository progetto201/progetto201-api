import unittest
import requests
import json
import testcases_date as tc_date


class TestApi(unittest.TestCase):

    def test_date(self):
        """
        Tests the /api/options/date.php script.

        A loop tests each test case.
        Each test case has a request method, headers and a payload.

        The response is always in JSON format, so if json.loads()
        fails, it means there is a problem with the PHP code and the test will fail 

        The response should be the expected one... if not the test will fail

        The status code should be the expected one if there's an error or 200 if there's no error.
        Otherwise the test will fail
        """
        # date.php url
        url = "http://localhost/api/options/date.php"

        # loop for each test case
        for testcase in tc_date.testcases_date:
            # request the url with the test case method, headers and payload
            response = requests.request(testcase["method"], url, headers=testcase["headers"], data = testcase["payload"])
            
            # try to convert the response in json data
            json_data = response.json()

            # check the expected output type: if it is a dictionary the expected result is one,
            # otherwise the output could be one of the many expected results
            if type(testcase["expected_output"]) == dict:
                # the json data should correspond to the expected result
                self.assertEqual(json_data, testcase["expected_output"])
            else:
                # check if one of the expected result is equal to the response
                check = False

                # loop for each possible output
                for expected_output in testcase["expected_output"]:
                    if json_data == expected_output:
                        # the output is equal to one of the possible outputs,
                        # the check has been successfull
                        check = True
                        break
                
                # fail test if check is False
                self.assertTrue(check)
            
            # if there are no errors in the reponse, the status code should be
            # the "htmlcode" of the first error.
            # No errors = status 200 OK
            if len(json_data["errors"]) > 0:
                self.assertEqual(response.status_code, json_data["errors"][0]["htmlcode"])
            else:
                self.assertEqual(response.status_code, 200)


if __name__ == "__main__":

    unittest.main()