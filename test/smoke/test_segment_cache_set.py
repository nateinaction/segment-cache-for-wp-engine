"""
Test to see if Segment Cache for WP Engine plugin sends cookie in response
"""
import subprocess
import unittest


class TestSegmentCacheSet(unittest.TestCase):
    """
    Verify plugin sets cookies
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


    def test_cookie_response(self):
        """
        Curl the page to see if cookie has been set
        """
        cookie_name = 'wpe-us'
        cookie_value = 'smoketest'
        run_cmd = 'curl --silent --output /dev/null --cookie-jar - {}'.format(self.test_url)
        cookie_jar = subprocess.check_output(run_cmd.split(), cwd=self.dir, universal_newlines=True, timeout=1)
        with self.subTest():
            self.assertIn(cookie_name, cookie_jar, 'The cookie "{}" was not set. {}'.format(cookie_name, cookie_jar))
            self.assertIn(cookie_value, cookie_jar, 'The cookie value was not "{}". {}'.format(cookie_value, cookie_jar))

