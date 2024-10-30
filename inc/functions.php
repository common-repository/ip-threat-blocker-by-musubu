<?php

####PLUGIN ADMIN AJAX HANDLER TO GENERATE API KEY################STARTS############
add_action( 'admin_footer', 'iptbbm_actionRegisterApiKey' ); // Write our JS below here

function iptbbm_actionRegisterApiKey()
{
?>
    <script type="text/javascript" >
    jQuery(document).ready(function($) {

//        console.log(ajaxurl);
        $('.iptable').on('click', '#saveApiKey', function(){

            console.log('ajaxurl');

            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            $.ajax({
                data: {
                    'action': 'iptbbm_register_api_key',
                    'apiKey': $('input[name="apiKeyForm"]').val()
                },
                type: 'POST',
                dataType: 'JSON',
                async: false,//FIXES FIREFOX POST AJAX GETTING ABORTED
                url: ajaxurl,
                beforeSend: function (xhr) {
                    $('#loadingImageSubmit').show();
                    $('#saveApiKey').prop('disable', true);
                }
            })
            .done(function (response) {
                $('#loadingImageSubmit').hide();
                $('#saveApiKey').prop('disable', false);

                if(response.status == 'success')
                {
                    $('#apiKeyContainer').slideDown('slow');
                    $('#apiKey').html($('input[name="apiKeyForm"]').val());
                }

                $('#apiKeyStatus').html(response.msg).parent().prop('class', response.msgClass).show();

            });

        });

    });
    </script>
<?php
}

add_action( 'wp_ajax_iptbbm_register_api_key', 'iptbbm_actionGenerateApiKeyHandler' );

function iptbbm_actionGenerateApiKeyHandler() {

    #DEFAULT ERROR MESSAGE
    $statusData = array('status' => 'error', 'msg' => 'We are experiencing a technical issue. Please contact <a href="mailto:support@musubu.co">support@musubu.co</a>.', 'msgClass' => 'error');

    #Run basic query using entered key, if error display error
    $sanitized_apiKey = strtoupper(sanitize_key($_POST[apiKey]));
    $response = wp_remote_get( "https://api.musubu.co/MusubuAPI/Musubu?IP=1.1.1.1&key=" . $sanitized_apiKey . "&level=verbose", ['timeout' => '10'] );

    ###HANDLE WP_ERROR###
    if( is_wp_error( $response ) ) {
        ###echo $response->get_error_message();
        echo json_encode($statusData);
        wp_die();
    }

    $httpCode = wp_remote_retrieve_response_code( $response );
    $apiBodyJson = wp_remote_retrieve_body( $response );

    $testQueryData = json_decode($apiBodyJson, true);

    if(empty($testQueryData['error']))
    {
        update_option("iptbbm_api_key", $sanitized_apiKey);
        $statusData = array('status' => 'success', 'apiKey' => $sanitized_apiKey, 'msg' => 'Site license activated!', 'msgClass' => 'updated' );
    }
    else if(!empty($testQueryData['error']['statusCode']) )
    {
        $statusData['msg'] = "This API Key is invalid. Please try a different API Key, or purchase one now <a target='_blank' href='https://wpthreat.co'>via this link</a>.";

        echo json_encode($statusData);
        wp_die();
    }

    echo json_encode($statusData);

    wp_die(); // this is required to terminate immediately and return a proper response
}

####PLUGIN ADMIN AJAX HANDLER TO GENERATE API KEY FROM EMAIL################STARTS############


function iptbbm_displayCountryDropdown($dbCountryCode)
{
    $countrySelectArray = iptbbm_getCountryCodesWithCountryNames();

    $selectCountryCode = '<select id="countryCode" name="iptbbm_country_code[]" multiple >';

    foreach ($countrySelectArray as $keyCountryCode => $valueCountryCode) {

        $selected = in_array($keyCountryCode, $dbCountryCode) ? 'selected' : '';

        $selectCountryCode .= "<option $selected value='$keyCountryCode'>$valueCountryCode</option>";
    }

    echo $selectCountryCode .= '</select>';

}


function iptbbm_getBlackListClassValues()
{
    return array(
        'all', 'apache', 'apt', 'blacklisted', 'botnet', 'botnetcnc', 'bruteforce', 'compromised', 'ftp', 'http', 'imap', 'mail', 'malware',
        'phishing', 'ransomware', 'shunned', 'sips', 'ssh', 'tor', 'tor hidden bridges', 'worm', 'zeus', 'unlisted',
    );
}

function iptbbm_getCountryCodesWithCountryNames()
{
    $countrySelectArray = array (
        'all' =>'All',
        'AF' => 'Afghanistan',
        'AX' => 'Åland Islands',
        'AL' => 'Albania',
        'DZ' => 'Algeria',
        'AS' => 'American Samoa',
        'AD' => 'Andorra',
        'AO' => 'Angola',
        'AI' => 'Anguilla',
        'AQ' => 'Antarctica',
        'AG' => 'Antigua and Barbuda',
        'AR' => 'Argentina',
        'AM' => 'Armenia',
        'AW' => 'Aruba',
        'AU' => 'Australia',
        'AT' => 'Austria',
        'AZ' => 'Azerbaijan',
        'BS' => 'Bahamas',
        'BH' => 'Bahrain',
        'BD' => 'Bangladesh',
        'BB' => 'Barbados',
        'BY' => 'Belarus',
        'BE' => 'Belgium',
        'BZ' => 'Belize',
        'BJ' => 'Benin',
        'BM' => 'Bermuda',
        'BT' => 'Bhutan',
        'BO' => 'Bolivia, Plurinational State of',
        'BQ' => 'Bonaire, Sint Eustatius and Saba',
        'BA' => 'Bosnia and Herzegovina',
        'BW' => 'Botswana',
        'BV' => 'Bouvet Island',
        'BR' => 'Brazil',
        'IO' => 'British Indian Ocean Territory',
        'BN' => 'Brunei Darussalam',
        'BG' => 'Bulgaria',
        'BF' => 'Burkina Faso',
        'BI' => 'Burundi',
        'KH' => 'Cambodia',
        'CM' => 'Cameroon',
        'CA' => 'Canada',
        'CV' => 'Cape Verde',
        'KY' => 'Cayman Islands',
        'CF' => 'Central African Republic',
        'TD' => 'Chad',
        'CL' => 'Chile',
        'CN' => 'China',
        'CX' => 'Christmas Island',
        'CC' => 'Cocos (Keeling) Islands',
        'CO' => 'Colombia',
        'KM' => 'Comoros',
        'CG' => 'Congo',
        'CD' => 'Congo, the Democratic Republic of the',
        'CK' => 'Cook Islands',
        'CR' => 'Costa Rica',
        'CI' => 'CÃƒÂ´te d\'Ivoire',
        'HR' => 'Croatia',
        'CU' => 'Cuba',
        'CW' => 'Curaçao',
        'CY' => 'Cyprus',
        'CZ' => 'Czech Republic',
        'DK' => 'Denmark',
        'DJ' => 'Djibouti',
        'DM' => 'Dominica',
        'DO' => 'Dominican Republic',
        'EC' => 'Ecuador',
        'EG' => 'Egypt',
        'SV' => 'El Salvador',
        'GQ' => 'Equatorial Guinea',
        'ER' => 'Eritrea',
        'EE' => 'Estonia',
        'ET' => 'Ethiopia',
        'FK' => 'Falkland Islands (Malvinas)',
        'FO' => 'Faroe Islands',
        'FJ' => 'Fiji',
        'FI' => 'Finland',
        'FR' => 'France',
        'GF' => 'French Guiana',
        'PF' => 'French Polynesia',
        'TF' => 'French Southern Territories',
        'GA' => 'Gabon',
        'GM' => 'Gambia',
        'GE' => 'Georgia',
        'DE' => 'Germany',
        'GH' => 'Ghana',
        'GI' => 'Gibraltar',
        'GR' => 'Greece',
        'GL' => 'Greenland',
        'GD' => 'Grenada',
        'GP' => 'Guadeloupe',
        'GU' => 'Guam',
        'GT' => 'Guatemala',
        'GG' => 'Guernsey',
        'GN' => 'Guinea',
        'GW' => 'Guinea-Bissau',
        'GY' => 'Guyana',
        'HT' => 'Haiti',
        'HM' => 'Heard Island and McDonald Islands',
        'VA' => 'Holy See (Vatican City State)',
        'HN' => 'Honduras',
        'HK' => 'Hong Kong',
        'HU' => 'Hungary',
        'IS' => 'Iceland',
        'IN' => 'India',
        'ID' => 'Indonesia',
        'IR' => 'Iran, Islamic Republic of',
        'IQ' => 'Iraq',
        'IE' => 'Ireland',
        'IM' => 'Isle of Man',
        'IL' => 'Israel',
        'IT' => 'Italy',
        'JM' => 'Jamaica',
        'JP' => 'Japan',
        'JE' => 'Jersey',
        'JO' => 'Jordan',
        'KZ' => 'Kazakhstan',
        'KE' => 'Kenya',
        'KI' => 'Kiribati',
        'KP' => 'Korea, Democratic People\'s Republic of',
        'KR' => 'Korea, Republic of',
        'KW' => 'Kuwait',
        'KG' => 'Kyrgyzstan',
        'LA' => 'Lao People\'s Democratic Republic',
        'LV' => 'Latvia',
        'LB' => 'Lebanon',
        'LS' => 'Lesotho',
        'LR' => 'Liberia',
        'LY' => 'Libya',
        'LI' => 'Liechtenstein',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'MO' => 'Macao',
        'MK' => 'Macedonia, the former Yugoslav Republic of',
        'MG' => 'Madagascar',
        'MW' => 'Malawi',
        'MY' => 'Malaysia',
        'MV' => 'Maldives',
        'ML' => 'Mali',
        'MT' => 'Malta',
        'MH' => 'Marshall Islands',
        'MQ' => 'Martinique',
        'MR' => 'Mauritania',
        'MU' => 'Mauritius',
        'YT' => 'Mayotte',
        'MX' => 'Mexico',
        'FM' => 'Micronesia, Federated States of',
        'MD' => 'Moldova, Republic of',
        'MC' => 'Monaco',
        'MN' => 'Mongolia',
        'ME' => 'Montenegro',
        'MS' => 'Montserrat',
        'MA' => 'Morocco',
        'MZ' => 'Mozambique',
        'MM' => 'Myanmar',
        'NA' => 'Namibia',
        'NR' => 'Nauru',
        'NP' => 'Nepal',
        'NL' => 'Netherlands',
        'NC' => 'New Caledonia',
        'NZ' => 'New Zealand',
        'NI' => 'Nicaragua',
        'NE' => 'Niger',
        'NG' => 'Nigeria',
        'NU' => 'Niue',
        'NF' => 'Norfolk Island',
        'MP' => 'Northern Mariana Islands',
        'NO' => 'Norway',
        'OM' => 'Oman',
        'PK' => 'Pakistan',
        'PW' => 'Palau',
        'PS' => 'Palestinian Territory, Occupied',
        'PA' => 'Panama',
        'PG' => 'Papua New Guinea',
        'PY' => 'Paraguay',
        'PE' => 'Peru',
        'PH' => 'Philippines',
        'PN' => 'Pitcairn',
        'PL' => 'Poland',
        'PT' => 'Portugal',
        'PR' => 'Puerto Rico',
        'QA' => 'Qatar',
        'RE' => 'Réunion',
        'RO' => 'Romania',
        'RU' => 'Russian Federation',
        'RW' => 'Rwanda',
        'BL' => 'Saint Barthélemy',
        'SH' => 'Saint Helena, Ascension and Tristan da Cunha',
        'KN' => 'Saint Kitts and Nevis',
        'LC' => 'Saint Lucia',
        'MF' => 'Saint Martin (French part)',
        'PM' => 'Saint Pierre and Miquelon',
        'VC' => 'Saint Vincent and the Grenadines',
        'WS' => 'Samoa',
        'SM' => 'San Marino',
        'ST' => 'Sao Tome and Principe',
        'SA' => 'Saudi Arabia',
        'SN' => 'Senegal',
        'RS' => 'Serbia',
        'SC' => 'Seychelles',
        'SL' => 'Sierra Leone',
        'SG' => 'Singapore',
        'SX' => 'Sint Maarten (Dutch part)',
        'SK' => 'Slovakia',
        'SI' => 'Slovenia',
        'SB' => 'Solomon Islands',
        'SO' => 'Somalia',
        'ZA' => 'South Africa',
        'GS' => 'South Georgia and the South Sandwich Islands',
        'SS' => 'South Sudan',
        'ES' => 'Spain',
        'LK' => 'Sri Lanka',
        'SD' => 'Sudan',
        'SR' => 'Suriname',
        'SJ' => 'Svalbard and Jan Mayen',
        'SZ' => 'Swaziland',
        'SE' => 'Sweden',
        'CH' => 'Switzerland',
        'SY' => 'Syrian Arab Republic',
        'TW' => 'Taiwan, Province of China',
        'TJ' => 'Tajikistan',
        'TZ' => 'Tanzania, United Republic of',
        'TH' => 'Thailand',
        'TL' => 'Timor-Leste',
        'TG' => 'Togo',
        'TK' => 'Tokelau',
        'TO' => 'Tonga',
        'TT' => 'Trinidad and Tobago',
        'TN' => 'Tunisia',
        'TR' => 'Turkey',
        'TM' => 'Turkmenistan',
        'TC' => 'Turks and Caicos Islands',
        'TV' => 'Tuvalu',
        'UG' => 'Uganda',
        'UA' => 'Ukraine',
        'AE' => 'United Arab Emirates',
        'GB' => 'United Kingdom',
        'US' => 'United States',
        'UM' => 'United States Minor Outlying Islands',
        'UY' => 'Uruguay',
        'UZ' => 'Uzbekistan',
        'VU' => 'Vanuatu',
        'VE' => 'Venezuela, Bolivarian Republic of',
        'VN' => 'Viet Nam',
        'VG' => 'Virgin Islands, British',
        'VI' => 'Virgin Islands, U.S.',
        'WF' => 'Wallis and Futuna',
        'EH' => 'Western Sahara',
        'YE' => 'Yemen',
        'ZM' => 'Zambia',
        'ZW' => 'Zimbabwe',
        'ZZ' => 'Unassigned',
    );

    return $countrySelectArray;

}
