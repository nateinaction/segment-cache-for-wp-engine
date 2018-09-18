"""
Test to see if Segment Cache for WP Engine plugin sends cookie in response
"""
import subprocess
import unittest


class TestSegmentCacheDisplayWithOutVar(unittest.TestCase):
    """
    Verify plugin responds correctly without $_SERVER var
    """
    def setUp(self):
        self.plugin_name = 'segment-cache-for-wp-engine'
        self.test_url = 'http://localhost/2018/09/18/segment-cache-test/'
        self.dir = '/var/www/html/wp-content/plugins/{}'.format(self.plugin_name)

        # load test content
        run_cmd = 'make load_test_content'
        subprocess.call(run_cmd.split(), cwd=self.dir)


    def tearDown(self):
        # reset db
        run_cmd = 'make setup_db'
        subprocess.call(run_cmd.split(), cwd=self.dir)


    def test_page_response_without_server_var(self):
        """
        Curl the page to see if shortcode rendered with no server var set
        """
        needle = 'Hello everybody else!'
        not_needle = 'Hello smoketest!'
        run_cmd = 'curl --silent {}'.format(self.test_url)
        response = subprocess.check_output(run_cmd.split(), cwd=self.dir, universal_newlines=True, timeout=1)
        with self.subTest():
            self.assertIn(needle, response, 'Page response does not contain "{}"'.format(needle))
            self.assertNotIn(not_needle, response, 'Page response contains "{}"'.format(not_needle))
