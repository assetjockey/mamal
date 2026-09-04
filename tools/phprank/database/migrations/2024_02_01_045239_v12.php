<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::update("UPDATE `settings` SET `name` = 'social_x' WHERE `name` = 'social_twitter';");

        DB::table('settings')->insert(
            [
                ['name' => 'captcha_driver', 'value' => (config('settings.captcha_site_key') && config('settings.captcha_secret_key') ? 'recaptcha' : '')],
                ['name' => 'auth_google', 'value' => ''],
                ['name' => 'auth_google_client_id', 'value' => ''],
                ['name' => 'auth_google_client_secret', 'value' => ''],
                ['name' => 'auth_microsoft', 'value' => ''],
                ['name' => 'auth_microsoft_client_id', 'value' => ''],
                ['name' => 'auth_microsoft_client_secret', 'value' => ''],
                ['name' => 'auth_apple', 'value' => ''],
                ['name' => 'auth_apple_client_id', 'value' => ''],
                ['name' => 'auth_apple_client_secret', 'value' => ''],
                ['name' => 'auth_apple_team_id', 'value' => ''],
                ['name' => 'auth_apple_key_id', 'value' => ''],
                ['name' => 'auth_apple_private_key', 'value' => ''],
                ['name' => 'social_discord', 'value' => ''],
                ['name' => 'social_github', 'value' => ''],
                ['name' => 'social_linkedin', 'value' => ''],
                ['name' => 'social_pinterest', 'value' => ''],
                ['name' => 'social_reddit', 'value' => ''],
                ['name' => 'social_threads', 'value' => ''],
                ['name' => 'social_tiktok', 'value' => ''],
                ['name' => 'social_tumblr', 'value' => ''],
                ['name' => 'report_guest', 'value' => ''],
                ['name' => 'force_https', 'value' => '1'],
                ['name' => 'contact_form', 'value' => '0'],
                ['name' => 'contact_address', 'value' => ''],
                ['name' => 'contact_email_public', 'value' => ''],
                ['name' => 'contact_phone', 'value' => ''],
            ]
        );

        DB::table('settings')->whereIn('name', ['captcha_keyword_generator','captcha_keyword_research','captcha_tools','captcha_registration','captcha_serp_checker','captcha_indexed_pages_checker','captcha_contact','captcha_registration','ad_dashboard_bottom','ad_dashboard_top','ad_projects_bottom','ad_projects_top','ad_report_bottom','ad_report_top','ad_reports_bottom','ad_reports_top','ad_tool_bottom','ad_tool_top','ad_tools_bottom','ad_tools_top'])->delete();

        Schema::create('categories', function (Blueprint $table) {
            $table->comment('');
            $table->string('id', 64)->unique();
            $table->string('name', 128);
        });

        DB::table('categories')->insert([
            ['id' => 'content','name' => 'Content'],
            ['id' => 'research','name' => 'Research'],
            ['id' => 'development','name' => 'Development']
        ]);

        Schema::dropIfExists('tools');

        Schema::create('tools', function (Blueprint $table) {
            $table->comment('');
            $table->increments('id');
            $table->string('slug', 64)->unique()->nullable();
            $table->string('name', 128);
            $table->string('icon', 32)->nullable();
            $table->string('category_id', 64)->index('category_id');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('views')->default(0);
            $table->timestamps();
        });

        DB::table('tools')->insert(
            [
                ['id' => '1','slug' => 'serp-checker','name' => 'SERP checker','icon' => 'manage-search','category_id' => 'research','description' => 'Get the top search engine results for a keyword.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '2','slug' => 'indexed-pages-checker','name' => 'Indexed pages checker','icon' => 'find-in-page','category_id' => 'research','description' => 'Check if a domain is indexed by search engines.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '3','slug' => 'keyword-research','name' => 'Keyword research','icon' => 'abc','category_id' => 'research','description' => 'Research the search volume, CPC, and competition of keywords.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '4','slug' => 'website-status-checker','name' => 'Website status checker','icon' => 'wifi-tethering','category_id' => 'research','description' => 'Check the status and availability of a website.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '5','slug' => 'ssl-checker','name' => 'SSL checker','icon' => 'lock','category_id' => 'research','description' => 'Check the SSL status and information of a domain.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '6','slug' => 'dns-lookup','name' => 'DNS lookup','icon' => 'dns','category_id' => 'research','description' => 'Lookup the list of DNS records of a domain.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '7','slug' => 'whois-lookup','name' => 'WHOIS lookup','icon' => 'info','category_id' => 'research','description' => 'Lookup ownership information of a domain.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '8','slug' => 'ip-lookup','name' => 'IP lookup','icon' => 'travel-explore','category_id' => 'research','description' => 'Get the location information for an IP address.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '9','slug' => 'reverse-ip-lookup','name' => 'Reverse IP lookup','icon' => 'searched-for','category_id' => 'research','description' => 'Get the hostname corresponding to an IP address.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '10','slug' => 'domain-ip-lookup','name' => 'Domain IP lookup','icon' => 'share-location','category_id' => 'research','description' => 'Get the IP address and location information of a domain.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '11','slug' => 'redirect-checker','name' => 'Redirect checker','icon' => 'shuffle','category_id' => 'development','description' => 'Analyze the complete redirect path of an URL.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '12','slug' => 'idn-converter','name' => 'IDN converter','icon' => 'language','category_id' => 'research','description' => 'Convert text to Punycode and Punycode to text.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '13','slug' => 'js-minifier','name' => 'JS minifier','icon' => 'js','category_id' => 'development','description' => 'Minify and improve the performance of JS code.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '14','slug' => 'css-minifier','name' => 'CSS minifier','icon' => 'css','category_id' => 'development','description' => 'Minify and improve the performance of CSS code.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '15','slug' => 'html-minifier','name' => 'HTML minifier','icon' => 'html','category_id' => 'development','description' => 'Minify and improve the performance of HTML code.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '16','slug' => 'json-validator','name' => 'JSON validator','icon' => 'data-object','category_id' => 'development','description' => 'Validate a JSON string and turn it into a readable format.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '17','slug' => 'password-generator','name' => 'Password generator','icon' => 'password','category_id' => 'development','description' => 'Generate a random password based on custom parameters.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '18','slug' => 'qr-generator','name' => 'QR generator','icon' => 'qr','category_id' => 'development','description' => 'Generate a customizable QR code for a text.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '19','slug' => 'user-agent-parser','name' => 'User-Agent parser','icon' => 'tab','category_id' => 'development','description' => 'Parse a User-Agent into readable individual components.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '20','slug' => 'md5-generator','name' => 'MD5 generator','icon' => 'tag','category_id' => 'development','description' => 'Generate a hash value using the MD5 algorithm.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '21','slug' => 'color-converter','name' => 'Color converter','icon' => 'style','category_id' => 'development','description' => 'Convert between HEX, RGB, and HLS color models.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '22','slug' => 'utm-builder','name' => 'UTM builder','icon' => 'label','category_id' => 'development','description' => 'Add UTM campaign parameters to an URL.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '23','slug' => 'url-parser','name' => 'URL parser','icon' => 'dataset-linked','category_id' => 'development','description' => 'Parse an URL into readable individual components.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '24','slug' => 'uuid-generator','name' => 'UUID generator','icon' => 'view-stream','category_id' => 'development','description' => 'Generate a version 4 universally unique identifier.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '25','slug' => 'lorem-ipsum-generator','name' => 'Lorem ipsum generator','icon' => 'notes','category_id' => 'content','description' => 'Generate placeholder text for paragraphs, sentences, and words.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '26','slug' => 'text-cleaner','name' => 'Text cleaner','icon' => 'format-clear','category_id' => 'content','description' => 'Clean HTML tags, spaces, and line breaks from a text.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '27','slug' => 'word-density-counter','name' => 'Word density counter','icon' => 'line-weight','category_id' => 'content','description' => 'Count the number and density of each word in a text.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '28','slug' => 'word-counter','name' => 'Word counter','icon' => 'pin','category_id' => 'content','description' => 'Count the number of words and letters in a text.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '29','slug' => 'case-converter','name' => 'Case converter','icon' => 'expand','category_id' => 'content','description' => 'Transform text to lower, upper, sentence, or capitalized case.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '30','slug' => 'text-to-slug-converter','name' => 'Text to slug converter','icon' => 'link','category_id' => 'content','description' => 'Convert text into a SEO friendly, readable URL slug.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '31','slug' => 'url-converter','name' => 'URL converter','icon' => 'url','category_id' => 'content','description' => 'Encode or decode a text to be used in URLs.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '32','slug' => 'base64-converter','name' => 'Base64 converter','icon' => 'border-bottom','category_id' => 'content','description' => 'Encode text to Base64, or decode Base64 to text.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '33','slug' => 'binary-converter','name' => 'Binary converter','icon' => 'money','category_id' => 'content','description' => 'Convert binary numbers to text and text to binary numbers.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '34','slug' => 'text-replacer','name' => 'Text replacer','icon' => 'find-replace','category_id' => 'content','description' => 'Find and replace all occurrences of a string in a text.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '35','slug' => 'text-reverser','name' => 'Text reverser','icon' => 'cached','category_id' => 'content','description' => 'Inverse the direction of the characters in a text.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '36','slug' => 'number-generator','name' => 'Number generator','icon' => '123','category_id' => 'content','description' => 'Generate a random number between a minimum and maximum value.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '37','slug' => 'uptime-calculator','name' => 'Uptime calculator','icon' => 'monitor-heart','category_id' => 'development','description' => 'Calculate the amount of time that a service is available.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '38','slug' => 'meta-tags-checker','name' => 'Meta tags checker','icon' => 'code','category_id' => 'research','description' => 'Analyze the meta tags information of an URL.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
                ['id' => '39','slug' => 'http-headers-checker','name' => 'HTTP headers checker','icon' => 'horizontal-split','category_id' => 'development','description' => 'Analyze the HTTP headers information of an URL.','views' => '0','created_at' => '2024-01-01 05:11:13','updated_at' => NULL],
            ]
        );

        foreach (DB::table('plans')->select('*')->cursor() as $row) {
            $features = json_decode($row->features, true);

            $features['tools'] = -1;

            unset($features['research_tools'], $features['content_tools'], $features['developer_tools']);

            DB::statement("UPDATE `plans` SET `features` = :features WHERE `id` = :id", ['features' => json_encode($features), 'id' => $row->id]);
        }

        Schema::table('pages', function ($table) {
            $table->string('language', 16)->after('visibility')->nullable();
        });

        DB::statement("UPDATE `pages` SET `language` = :language", ['language' => config('settings.locale')]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
