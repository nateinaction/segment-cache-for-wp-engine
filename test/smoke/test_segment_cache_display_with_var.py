"""
Test to see if Segment Cache for WP Engine plugin sends cookie in response
"""
import subprocess
import unittest


class TestSegmentCacheDisplayWithVar(unittest.TestCase):
    """
    Verify plugin responds correctly with $_SERVER var set to 'smoketest'
    """
    def setUp(self):
        self.plugin_name = 'segment-cache-for-wp-engine'
        self.test_url = 'http://localhost/2018/09/18/segment-cache-test/'
        self.dir = '/var/www/html/wp-content/plugins/{}'.format(self.plugin_name)

        # load test content and add mu plugin
        run_cmd = 'make load_test_content place_test_mu_plugin'
        subprocess.call(run_cmd.split(), cwd=self.dir)


    def tearDown(self):
        # remove mu plugin and reset db
        run_cmd = 'make remove_test_mu_plugin setup_db'
        subprocess.call(run_cmd.split(), cwd=self.dir)


    def test_vary_header_response(self):
        """
        Curl the page to see if vary header has been set
        """
        needle = 'Vary: X-WPENGINE-SEGMENT'
        run_cmd = 'curl --silent -IL {}'.format(self.test_url)
        response_headers = subprocess.check_output(run_cmd.split(), cwd=self.dir, universal_newlines=True, timeout=1)
        with self.subTest():
            self.assertIn(needle, response_headers, 'The vary header has not been set. {}'.format(response_headers))


    def test_page_response_with_server_var(self):
        """
        Curl the page to see if shortcode rendered with 'smoketest' server var set
        """
        needle = 'Hello smoketest!'
        not_needle = 'Hello everybody else!'
        run_cmd = 'curl --silent {}'.format(self.test_url)
        response = subprocess.check_output(run_cmd.split(), cwd=self.dir, universal_newlines=True, timeout=1)
        with self.subTest():
            self.assertIn(needle, response, 'Page response does not contain "{}"'.format(needle))
            self.assertNotIn(not_needle, response, 'Page response contains "{}"'.format(not_needle))
