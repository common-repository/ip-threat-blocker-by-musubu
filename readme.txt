=== IP Threat Blocker ===
Contributors: Musubu
Tags: security, firewall, malware scanner, web application firewall, antivirus, block hackers, country blocking, clean hacked site, blacklist, waf, login security
Requires at least: 5.0.0
Tested up to: 5.4
Stable tag: 1.1.1
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl.html

IP Threat Blocker for Wordpress by Musubu

== Description ==

### Now with Free Base Threat Blocker!

Musubu's IP Threat Blocker for Wordpress leverages the full power of the Musubu API to dynamically screen incoming IP addresses to your website for cybersecurity threat ratings, threat types, countries and networks of origin allowing the user to automatically (“set it and forget it”) or manually block IPs by threat score, type, or country.

Simply put, our plugin is the easiest way to avoid a hack or breach without having to know anything about cybersecurity. Website owners can just install our plugin, then set it to automatically block IPs observed and associated with high-threat cybercrime vectors, such as Ransomware, Bots, Phishing, Spam, TOR, malware, and dozens of more exploit types.

Stop cyber threats at the door before they can do damage to your site, data, customers, customer data, or eCommerce revenues. Musubu's IP Threat Blocker for Wordpress allows for easy blocking or unblocking of IPs with simple-to-use controls and also connects directly (and free) to MusubuApp, the leading IP & Network Threat Intelligence Web Portal for researching or drilling into more details on any given IP address.

Musubu is different in that it provides highly-enriched information and scoring algorithms designed to give you a sense of real threats posed by a network. It's not a simple threat aggregation engine matching IP addresses like many of the solutions available today but is focused on the network of origin “in context” as well as what threat is posed by the entire subnet and broader environment of origin.

Our plugin uses the following API results to perform effective blocking:

threat_potential_score_pct – Numeric threat score between 0-100. The Score is calculated using “blacklist class”, “blacklist neighbors,” number of recent observations and country of origin.

threat_classification – Classification derived from “threat potential score pct”

High – Threat score >70
Medium – Threat score from >40 but <70
Nuisance – Threat score <40
Low – Any IP unlisted with a threat score <20

Blacklist_class (i.e., “Severity of Threat Types”) – Field classifying the specific threat vector that has been identified. Contains one of the following values: apache, blacklisted, botnet, botnetcnc, brute force, compromised, ftp, http, imap, mail, malware, phishing, ransomware, shunned, sips, ssh, TOR, worm, zeus

Blacklist_class_cnt (i.e., “Total No. of Distinct Threat Classes”) – Field providing the number of sources which have identified the address as malicious.

Blacklist_network_neighbors (i.e., “Similar Subnet Threats”) – Field providing the number of addresses present on the same subnet which have been identified as malicious.

Blacklist_observations (i.e., “Total Subnet Threat Volume”) – Field providing the number of observations (of this IP) in the last 90 days.

PS: You’ll need a Musubu.io API key to use it live on your site. Paid subscriptions are available for businesses, personal, and commercial sites here at [https://wpthreat.co](https://wpthreat.co).

== Installation ==

Secure your website by following the steps below to install IP Threat Blocker by Musubu:

1. In the WordPress Dashboard, navigate to the 'Plugins' menu. Click on the 'Add New' button. Search for 'IP Threat Blocker by Musubu' and click the 'Install Now' button, or click on the 'Upload Plugin' button to upload a ZIP file of this plugin. Once the plugin is installed click the 'Activate' button.
2. If you haven't already purchased an API key, you will need to do so now. You can purchase an API key [here](https://www.wpthreat.co). We will email you a key.
3. To change the options and settings of the IP Threat Blocker by Musubu plugin, navigate to the WordPress 'Settings' menu and click on the 'IP Threat Blocker' menu item. First, you will need to enter your API key. Check your email and copy the API key that was sent to you. In the Settings section paste your Musubu API key into the form field and click the 'Save Changes' button.
4. Now that the plugin is enabled with a key, it will be set to 'Automatic' Block Mode which will automatically set the threat score, threat class, and country thresholds to block any matching IP addresses from viewing your website. Switching to 'Manual' mode allows you to manually set thresholds to block IPs by threat score, threat class, or country of origin.
5. On the IP Threat Blocker settings page, the Blocked IP Addresses table will populate with any IP addresses that have been blocked from viewing your website. In the table, you can click on any IP address shown to view more details about that IP address's location, network type, network owner, street address and more for free in Musubu's industry-leading IP & Network Threat Intelligence web portal, [MusubuApp](https://musubuapp.co). Any blocked IP addresses in the list can be individually unblocked.

To install the IP Threat Blocker by Musubu on WordPress Multi-Site:

1. In the WordPress Dashboard, navigate to 'My Sites > Network Admin > Plugins.' Click on the 'Add New' button. Search for 'IP Threat Blocker by Musubu' and click the 'Install Now' button, or click on the 'Upload Plugin' button to upload a ZIP file of this plugin. Once the plugin is installed click the 'Network Activate' button to activate the plugin on all of the websites within this install, or in order to only activate the plugin on specific websites navigate to 'My Sites > Site Name > Dashboard', then navigate to 'Plugins', find the IP Threat Blocker by Musubu plugin in the list and click the 'Activate' link.
2. If you haven't already purchased an API key, you will need to do so now. You can purchase an API key [here](https://www.wpthreat.co). We will email you a key.
3. The options and settings of the IP Threat Blocker by Musubu plugin can be changed on a site by site basis. Navigate to the website's 'Settings' menu by navigating to 'My Sites > Site Name > Dashboard' and then navigating to 'Settings' and clicking on the 'IP Threat Blocker' menu item. First, you will need to enter your API key. Check your email and copy the API key that was sent to you. In the Settings section paste your Musubu API key into the form field and click the 'Save Changes' button.
4. Now that the plugin is enabled with a key for this one website, the plugin will be set to 'Automatic' Block Mode which will automatically set the threat score, threat class, and country thresholds to block any matching IP addresses from viewing your website. Switching to 'Manual' mode allows you to manually set thresholds to block IPs by threat score, threat class, or country of origin.
5. On the IP Threat Blocker settings page, the Blocked IP Addresses table will populate with any IP addresses that have been blocked from viewing your website. In the table, you can click on any IP address shown to view more details about that IP address's location, network type, network owner, street address and more for free in Musubu's industry-leading IP & Network Threat Intelligence web portal, [MusubuApp](https://musubuapp.co). Any blocked IP addresses in the list can be individually unblocked.
6. Repeat steps 3 and 4 for each website on the network that will be utilizing the plugin.

== Frequently Asked Questions ==

[Visit our website to access our official documentation and obtain the Wordpress plugin API key](https://wpthreat.co)

= How does IP Threat Blocker by Musubu protect sites from attackers? =

The IP Threat Blocker by Musubu plugin provides the best web application firewall protection available for your website. Using the powerful and popular [https://musubuapp.co](https://musubuapp.co) cyber threat intelligence API, it works as a firewall detecting each visiting IP address to automatically call the API to get the IPs threat score (Low:0 - High:100), type (see example list below) and country of origin to block potential cyber threats based on user settings.

The plugin also uses the API data to display cyber threat types by each IP, such as:

- Ransomware
- Phising
- Spamware
- Tor Exit Node Traffic
- Malware
- Botnets
- Many more

Musubu's plugin can be set to block threats by type, as well as score and country of origin.

= How often is IP Threat Blocker by Musubu updated? =

We update our plugin often and, since the plugin uses Musubu API, all threat scores and types per IP address are built dynamically as they come in; every day, all day. That way, an IP address that wasn't a threat last week but now is a threat today will be surfaced for automatic or manual blocking. No updates required.

= Can the plugin be used for GDPR blocking? =

Yes, you can use IP Threat Blocker by Musubu to block traffic from specific countries.

= How do customers get support? =

Paid subscribers can always get support 8 X 5 from 0900 CST to 1700 CST by using suppport@musubu.co or by visiting our Support site [support.musubu.co]

= IP Threat Blocker by Musubu service =

This plugin uses Musubu web services in order to provide up to the minute threat blocking rules. The plugin will verify with the Musubu API all potential threats that attempt to access your WordPress site.

For more information on Musubu web services please review our privacy policy, terms of service, and visit the web interface for our API:
* [Musubu Web App](https://musubuapp.co/login)
* [Musubu Terms of Service](https://musubu.co/terms/)
* [Musubu Privacy Policy](https://musubu.co/privacy-policy/)

== Screenshots ==

1. Musubu API Key.
2. Settings.
3. Blocked IP Addresses.
4. IP specific details shown on [Musubuapp.co](https://musubuapp.co).
