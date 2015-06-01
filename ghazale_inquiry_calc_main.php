<?php
/*
Plugin Name: Inquiry Calc
Plugin URI:  http://ghazale.co.nf/index.php/2015/05/20/inquiry-calc-plugin/
Description: Perfect for user inquiries about the cost of service or product. Calculates the final cost immediately in real time based on the fees you have set in the options. It emails the final cost/cost-breakdown to user upon request. And it also updates admin about new inquiries as well. Admin can see what inquiries have been made and also can see what the user has selected and what the cost structure has been when the user used the inquiry calculator on your website.
Author: Ghazale Shirazi
Version: 1.1
Author URI: http://ghazale.co.nf


    Copyright 2015  Ghazale Shirazi  (email : ghsh88@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

require_once("ghazale_inquiry_msg_session.php");

/**
 * creates questions table upon activating the plugin
 */

function ghazale_inquiry_create_table(){
    global $wpdb;
    $q_table = $wpdb->prefix ."ghazale_inquiry_q";
    $c_table = $wpdb->prefix . "ghazale_inquiry_c";
    if($wpdb->get_var('SHOW TABLES LIKE ' . $q_table) !=$q_table){
        $sql = "CREATE TABLE {$q_table} (id INTEGER (10) UNSIGNED AUTO_INCREMENT, inquiry_question VARCHAR(255), question_cost INT(10), PRIMARY KEY  (id) )";
        $wpdb->query($sql);
        add_option('ghazale_q_table_version','1.0');
    }

    if($wpdb->get_var('SHOW TABLES LIKE ' . $c_table) !=$c_table){
        $sql = "CREATE TABLE {$c_table} (id INTEGER (10) UNSIGNED AUTO_INCREMENT, question_id INTEGER (10), inquiry_condition VARCHAR(255), condition_cost INT(10), PRIMARY KEY (id))";
        $wpdb->query($sql);
        add_option('ghazale_c_table_version','1.0');
    }
}
register_activation_hook(__FILE__, 'ghazale_inquiry_create_table');

/**
 * Shows the questions that have been saved in the table
 */

function ghazale_inquiry_show_qc_table(){
    global $wpdb;
    $q_table = $wpdb->prefix ."ghazale_inquiry_q";
    $c_table = $wpdb->prefix . "ghazale_inquiry_c";
    $questions_query = "SELECT * FROM {$q_table}";
    $questions = $wpdb->get_results($questions_query,ARRAY_A);
    $conditions_query = "SELECT * FROM {$c_table}";
    $conditions = $wpdb->get_results($conditions_query,ARRAY_A);
    ?>
    <style>
        table {
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
            padding: 5px;
        }
    </style>
    <h2>Questions and Related Conditions</h2>
    <input type="checkbox" id="ghazale_inquiry_guide_show_hide" /> Show/Hide Guide Notes
    <div id="ghazale_inquiry_guide_notes">
    <ol style="color: #5C5C5C">
        <p><h3 style="color: #5C5C5C">Guide Notes</h3></p>
        <li>Add the Questions/Conditions and their corresponding costs from the Inquiry Calc menu.</li>
        <li>After you setup your General Settings (Currency, Min Cost, etc.) and added the questions/conditions with their cost, just put the shortcode <strong>[inquiry-calc]</strong> on any page or post and you're ready to go.</li>
    </ol>
    </div>
    <?php echo "<div style='background-color: #fff; line-height: 2em;border-left: 5px solid #009900; font-size: 14px'>" . ghazale_inquiry_update_message() . "</div><br><br>"; ?>
    <table>
        <tr>
            <th>Delete | Edit</th>
            <th>Questions</th>
            <th>Question's Cost</th>
            <th>Related Conditions</th>
            <th>Condition's cost</th>
        </tr>
        <?php
        foreach($questions as $question){
            ?>
            <tr>
                <td><a href="<?php echo get_admin_url(); ?>admin.php?page=ghazale-questions-table&ghazaledel=<?php echo $question['id']; ?>" onclick="return confirm('Are you sure? (THIS ACTION CANNOT BE UNDONE)');">Delete</a> &nbsp; | &nbsp; <a href="<?php echo get_admin_url(); ?>admin.php?page=ghazale-edit-qc&id=<?php echo $question['id']; ?>">Edit</a> </td>
                <td>
                    <?php
                    echo $question['inquiry_question'];
                    ?>
                </td>
                <td>
                    <?php
                    echo get_option('ghazale_inquiry_currency')." ".$question['question_cost'];
                    ?>
                </td>
                <td>
                    <?php
                    foreach($conditions as $condition){
                        if($condition['question_id'] == $question['id']) {
                            echo $condition['inquiry_condition'] . "<br>";
                        }
                    }
                    ?>
                </td>
                <td>
                    <?php
                    foreach($conditions as $condition) {
                        if ($condition['question_id'] == $question['id']) {
                            echo get_option('ghazale_inquiry_currency')." ".$condition['condition_cost'] . "<br>";
                        }
                    }
                    ?>
                </td>
            </tr>
        <?php
        }
        ?>
    </table>
<?php

}

function ghazale_inquiry_show_qc_menu(){
    add_menu_page('Inquiry Calc', 'Inquiry Calc', 'manage_options', 'ghazale-questions-table', 'ghazale_inquiry_show_qc_table', plugin_dir_url(__FILE__).'images/ic_inq.png');
    $page_suffix = add_submenu_page('ghazale-questions-table','Inquiry Questions','Inquiry Questions','manage_options','ghazale-questions-table','ghazale_inquiry_show_qc_table');
    add_action('admin_print_scripts-'. $page_suffix, 'ghazale_inquiry_qc_admin_scripts');
}

add_action('admin_menu','ghazale_inquiry_show_qc_menu');

function ghazale_inquiry_qc_admin_scripts(){
    wp_enqueue_script('ghazale_inquiry_qc_admin_script');
}
function ghazale_inquiry_qc_admin_register_script(){
    wp_register_script('ghazale_inquiry_qc_admin_script',plugins_url('/js/ghazale_inquiry_backend_guide_notes.js',__FILE__),array('jquery'));
}

add_action('admin_init','ghazale_inquiry_qc_admin_register_script');
/**
 * options for general settings
 */
function ghazale_inquiry_general_settings(){
    ?>
    <h2>General Settings</h2>

    <form action="options.php" method="post" id="inquiry_general_settings">
        <?php settings_errors(); ?>
        <?php settings_fields('ghazale_inquiry_options_group'); ?>
        Currency: <input type="text" name="ghazale_inquiry_currency" id="ghazale_inquiry_currency" value="<?php echo esc_attr(get_option('ghazale_inquiry_currency')); ?>"/><i> Write currency Symbol like: $, £, €,... or Currency Code like: USD, GBP, EUR, AED,...</i><br><br>
        Minimum Flat Cost: <input type="number" step="any" min="0" name="ghazale_inquiry_min_cost" id="ghazale_inquiry_min_cost" value="<?php echo esc_attr(get_option('ghazale_inquiry_min_cost')); ?>"/><i> Other costs will be added to this minimum cost (If you don't have a minimum cost, leave it blank)</i><br><br>
        <hr>
        <h2>Inform Admin</h2>
        <input type="checkbox" name="ghazale_inquiry_admin_mail_checkbox" id="ghazale_inquiry_admin_mail_checkbox" value="1" <?php checked(get_option('ghazale_inquiry_admin_mail_checkbox'),1); ?> /> Inform admin if the user chooses to receive the inquiry result/cost-breakdown by email. <br><br>
        Admin Email : <input type="email" name="ghazale_inquiry_admin_mail" id="ghazale_inquiry_admin_mail" value="<?php
        if(get_option('ghazale_inquiry_admin_mail')){
            echo get_option('ghazale_inquiry_admin_mail');
        }else{
            echo get_option('admin_email');
        }
        ?>" /><i> This email will also be in the "From:" section of the email when user receive the cost-breakdown in their email.</i><br><br>
        <hr>
        <h2>Page Redirection</h2>
        Thank You Page: <input type="text" name="ghazale_inquiry_thankyou_url" id="ghazale_inquiry_thankyou_url" value="<?php echo esc_attr(get_option('ghazale_inquiry_thankyou_url')); ?>" placeholder="Should start with http"/><i> Custom Thank You/ Confirmation page URL (leave it blank if you don't wanna redirect)</i><br><br>

        <input type="submit" name="submit_inquiry_general" value="Submit">

    </form>
<?php
}

function ghazale_inquiry_general_settings_menu(){
    add_submenu_page('ghazale-questions-table','General Settings','General Settings','manage_options','ghazale-inquiry-general-settings','ghazale_inquiry_general_settings');
}

add_action('admin_menu','ghazale_inquiry_general_settings_menu');

/**
 * options page for adding inquiry questions
 */
function ghazale_inquiry_add_questions(){
    ?>
    <h2>Add Inquiry Questions</h2>
    <?php echo "<div style='background-color: #fff; line-height: 2em;border-left: 5px solid #009900; font-size: 14px'>" . ghazale_inquiry_update_message() . "</div><br><br>"; ?>
    <form action="" id="inquiry_questions" method="post">
        Inquiry Question: <input type="text" name="ghazale_inquiry_question" id="ghazale_inquiry_question"  size="40" /><i> Write a question that can be answered with "Yes" or "No"</i><br><br>
        Cost: <input type="number" step="any" min="0" name="ghazale_inquiry_q_cost" id="ghazale_inquiry_q_cost" /><i> This cost will be added to the total cost if the user answers "Yes" to this question</i><br><br>
        <input type="submit" name="submit_question" value="Add Question">
    </form>
<?php
}

function ghazale_inquiry_questions_menu(){
    add_submenu_page('ghazale-questions-table','Add Question', 'Add Question', 'manage_options', 'ghazale-add-questions', 'ghazale_inquiry_add_questions');
}

add_action('admin_menu', 'ghazale_inquiry_questions_menu');


/**
 * adds questions to the ghazale_inquiry_q table
 */

function ghazale_inquiry_insert_questions(){
    global $wpdb;
    $q_table = $wpdb->prefix ."ghazale_inquiry_q";
    if(isset($_POST['submit_question']) && trim($_POST['ghazale_inquiry_question']) !=""){
        $wpdb->insert($q_table, array('inquiry_question'=> stripcslashes(trim($_POST['ghazale_inquiry_question'])), 'question_cost' => stripcslashes(trim($_POST['ghazale_inquiry_q_cost']))), array('%s'));
        $_SESSION['message'] = ' Added Successfully';
    }elseif(isset($_POST['submit_question']) && trim($_POST['ghazale_inquiry_question']) ==""){
        $_SESSION['message'] =' Please Write a Question to Add';
    }
}
add_action('init','ghazale_inquiry_insert_questions');


/**
 * options page for adding conditions to questions
 */

function ghazale_inquiry_add_conditions(){
    global $wpdb;
    $q_table = $wpdb->prefix ."ghazale_inquiry_q";
    $questions = "SELECT * FROM {$q_table}";
    $questions_query = $wpdb->get_results($questions, ARRAY_A);
//    var_dump($questions_query);
//    foreach($questions_query as $questions) {
//        echo $questions['inquiry_question'];
//    }

//    $question_id_query= "SELECT id FROM {$q_table} WHERE inquiry_question='Do you need custom theme design?'";
//    $question_id = $wpdb->get_var($question_id_query);
//    var_dump($question_id);
//    echo $question_id;
    ?>

    <h2> Add Conditions to Questions </h2>

    <form action="" id="inquiry_conditions" method="post">
        <?php echo "<div style='background-color: #fff; line-height: 2em;border-left: 5px solid #009900; font-size: 14px'>" . ghazale_inquiry_update_message() . "</div><br><br>"; ?>
        Question: <select id="select_question" name="select_question">
            <option value="0">-- Please Select --</option>
            <?php
            foreach($questions_query as $question){
                echo "<option value= \"".esc_attr($question['inquiry_question'])."\" >";
                echo $question['inquiry_question'];
                echo "</option>";
            }
            ?>
        </select><i> Select the question you wish to add condition to it.</i><br><br>
        Conditional question: <input type="text" name="ghazale_inquiry_condition" id="ghazale_inquiry_condition" size="40" /><i> Write the conditional question that can be answered with "Yes" or "No"</i><br><br>
        Cost: <input type="number" step="any" min="0" name="ghazale_inquiry_c_cost" id="ghazale_inquiry_c_cost" /><i> This cost will be added to total cost if the user answers "yes" to the conditional question.</i><br><br>
        <input type="submit" name="submit_condition" value="Add Condition">
    </form>

<?php
}
function ghazale_inquiry_conditions_menu(){
    add_submenu_page('ghazale-questions-table','Add Condition','Add Condition','manage_options','ghazale-add-conditions','ghazale_inquiry_add_conditions');
}
add_action('admin_menu','ghazale_inquiry_conditions_menu');
/**
 * adds conditions to ghazale_inquiry_c table
 */
function ghazale_inquiry_insert_conditions(){
    global $wpdb;
    $c_table = $wpdb->prefix ."ghazale_inquiry_c";
    $q_table = $wpdb->prefix ."ghazale_inquiry_q";
    if(esc_attr($_POST['select_question']) != "0") {
        $question_id_query = "SELECT id FROM {$q_table} WHERE inquiry_question=" . "'" . esc_attr($_POST['select_question']) . "'";
        $question_id = $wpdb->get_var($question_id_query);
        if (isset($_POST['submit_condition'])) {
            $wpdb->insert($c_table, array('question_id' => $question_id, 'inquiry_condition' => stripcslashes(trim($_POST['ghazale_inquiry_condition'])), 'condition_cost' => stripcslashes(trim($_POST['ghazale_inquiry_c_cost']))), array('%s'));
            $_SESSION['message'] = ' Added Successfully';
        }
    }else{
        $_SESSION['message'] = ' Please Select a Question.';
    }
}

add_action('init','ghazale_inquiry_insert_conditions');

/**
 * options for conditions, currency and the min cost
 */

function ghazale_inquiry_init(){
    register_setting('ghazale_inquiry_options_group','ghazale_inquiry_currency');
    register_setting('ghazale_inquiry_options_group','ghazale_inquiry_min_cost');
    register_setting('ghazale_inquiry_options_group','ghazale_inquiry_admin_mail');
    register_setting('ghazale_inquiry_options_group','ghazale_inquiry_thankyou_url');
    register_setting('ghazale_inquiry_options_group','ghazale_inquiry_admin_mail_checkbox');
}
add_action('admin_init','ghazale_inquiry_init');

/**
 * edit page for updating questions and conditions
 */
function ghazale_inquiry_edit_qc(){
    global $wpdb;
    $c_table = $wpdb->prefix ."ghazale_inquiry_c";
    $q_table = $wpdb->prefix ."ghazale_inquiry_q";
    $question_query = "SELECT * FROM {$q_table} WHERE id={$_GET['id']}";
    $question = $wpdb->get_results($question_query,ARRAY_A);

    $conditions_query = "SELECT * FROM {$c_table} WHERE question_id={$_GET['id']}";
    $conditions = $wpdb->get_results($conditions_query,ARRAY_A);

    ?>
    <h2>Edit Question / Conditions</h2>
    <?php echo "<div style='background-color: #fff; line-height: 2em;border-left: 5px solid #009900; font-size: 14px'>" . ghazale_inquiry_update_message() . "</div><br><br>"; ?>
    <form action="" method="post" id="ghazale_edit_qc">
        <h3>Edit Question</h3>
        <strong>Question:</strong> <input type="text" name="ghazale_inquiry_q_update" size="40" value="<?php echo $question[0]['inquiry_question']; ?>"/><br><br>
        <strong>Question's Cost:</strong> <i><?php echo get_option('ghazale_inquiry_currency'); ?></i> <input type="number" step="any" min="0" name="ghazale_inquiry_q_cost_update" value="<?php echo $question[0]['question_cost'] ?>"/><br><br>
        <?php
        if(count($conditions) >0) {
            ?>
            <hr>
            <h3>Edit Related Conditions</h3>
            <?php
            foreach($conditions as $condition){
                ?>
                <strong>Condition:</strong> <input type="text" name="ghazale_inquiry_c_update_<?php echo $condition['id']; ?>" size="40" value="<?php echo $condition['inquiry_condition']; ?>" /><br><br>
                <strong>Condition's Cost:</strong> <i><?php echo get_option('ghazale_inquiry_currency'); ?></i> <input type="number" step="any" min="0" name="ghazale_inquiry_c_cost_update_<?php echo $condition['id']; ?>" value="<?php echo $condition['condition_cost']; ?>" /><br><br>
                --------------------------------------------------- <br>
            <?php
            }
        }
        ?>

        <input type="submit" name="ghazale_edit_qc" id="ghazale_edit_qc" value="Update">
        <a href="<?php echo get_admin_url(); ?>admin.php?page=ghazale-questions-table" style="text-decoration: none"><input type="button" value="Back to Table"></a>
    </form>
<?php
}
function ghazale_edit_qc_menu(){
    add_submenu_page(null,'Edit Questions/Conditions','Edit Questions/Conditions','manage_options','ghazale-edit-qc','ghazale_inquiry_edit_qc');
}
add_action('admin_menu','ghazale_edit_qc_menu');

/**
 * updates questions and conditions in database table
 */

function ghazale_inquiry_update_qc(){

    global $wpdb;
    $c_table = $wpdb->prefix ."ghazale_inquiry_c";
    $q_table = $wpdb->prefix ."ghazale_inquiry_q";


    if(isset($_POST['ghazale_edit_qc'])){
        $id= $_GET['id'];
        $conditions_query= "SELECT * FROM {$c_table} WHERE question_id={$id}";

        $conditions = $wpdb->get_results($conditions_query,ARRAY_A);
        $wpdb-> update($q_table,array('inquiry_question'=>stripcslashes(trim($_POST['ghazale_inquiry_q_update'])), 'question_cost'=>stripcslashes(trim($_POST['ghazale_inquiry_q_cost_update']))),array('id' => $_GET['id']),array('%s'));
        foreach($conditions as $condition) {
//             $conditions_query_inside = "UPDATE {$c_table} SET inquiry_condition=" . "'".stripcslashes(trim($_POST['ghazale_inquiry_c_update_' . $condition['id']])) ."'". ", condition_cost=". stripcslashes(trim($_POST['ghazale_inquiry_c_cost_update_' . $condition['id']])) . " WHERE id=". $condition['id'];
//             $wpdb->query($conditions_query_inside);
            $wpdb->update($c_table,array('inquiry_condition'=>stripcslashes(trim($_POST['ghazale_inquiry_c_update_' . $condition['id']])),'condition_cost'=>stripcslashes(trim($_POST['ghazale_inquiry_c_cost_update_' . $condition['id']]))),array('id' => $condition['id']),array('%s'));
        }
        $_SESSION['message']= 'Updated Successfully';
    }
}
add_action('init','ghazale_inquiry_update_qc');

/**
 * deletes selected question and its related conditions
 */
function ghazale_inquiry_delete_qc(){
    global $wpdb;
    $c_table = $wpdb->prefix ."ghazale_inquiry_c";
    $q_table = $wpdb->prefix ."ghazale_inquiry_q";
    if(isset($_GET['ghazaledel'])){
        $del = $_GET['ghazaledel'];
        $wpdb->delete($q_table,array('id'=> $del));
        $conditions_query= "SELECT * FROM {$c_table} WHERE question_id={$del}";
        $conditions = $wpdb->get_results($conditions_query,ARRAY_A);
        if(count($conditions)>0) {
            foreach ($conditions as $condition) {
                $wpdb->delete($c_table, array('id' => $condition['id']));
            }
        }
        $_SESSION['message']= 'Successfully Deleted';
    }
}
add_action('init','ghazale_inquiry_delete_qc');

/**
 * shows questions/conditions on front-end by a shortcode
 * emails the cost breakdown to user and also updates admin if the user chooses to receive cost-breakdown
 */
function ghazale_inquiry_frontend_shortcode(){

    global $wpdb;
    $q_table = $wpdb->prefix ."ghazale_inquiry_q";
    $c_table = $wpdb->prefix ."ghazale_inquiry_c";
    $questions_query = "SELECT * FROM {$q_table} ORDER BY id ASC";
    $questions = $wpdb-> get_results($questions_query,ARRAY_A);
    $conditions_query = "SELECT * FROM {$c_table} ORDER BY id ASC";
    $conditions = $wpdb->get_results($conditions_query,ARRAY_A);

    $output = "<div style='background-color: #06B9FD; color: #fff; line-height: 2em;border-left: 5px solid #00008b; font-size: 14px'>" . ghazale_inquiry_update_message() . "</div><br>";
    $output .= "<br>";
    $output .= "<img src=\"".plugins_url('images/back28.png',__FILE__)."\" id=\"ghazale_inquiry_go_left\" style=\"cursor:pointer;\"> <img src=\"". plugins_url('images/right127.png',__FILE__)." \" id=\"ghazale_inquiry_go_right\" style=\"cursor:pointer; margin-left: 372px\">";
    if(trim(get_option('ghazale_inquiry_min_cost'))) {

        $output .= "<br>Minimum Flat Cost:" . get_option('ghazale_inquiry_currency') ."<strong class=\"ghazale_inquiry_min_flat_cost\"> ". esc_attr(get_option('ghazale_inquiry_min_cost'))."</strong>";
    }
    $output .= "<br><strong>Total Cost: </strong>" . get_option('ghazale_inquiry_currency')."<strong class=\"ghazale_inquiry_final_cost\">";
        if(trim(get_option('ghazale_inquiry_min_cost'))) {
            $output .= esc_attr(get_option('ghazale_inquiry_min_cost'));
        }else{
            $output .= "0";
        }
        $output .="</strong>";

    $output .= "<form action=\"\" method=\"post\" id=\"ghazale_inquiry_send_cost_breakdown\">
    <div class=\"ghazale_question_box\">

    <div>";

    if(count($questions)>0) {
        $ghazale_inquiry_questions = array();
        $ghazale_frontend_q_cost = array();
        foreach ($questions as $question) {
            $output .= "<div class =\"ghazale_inquiry_q_and_c\">
                <div class=\"ghazale_inquiry_questions\">";

                    $output .= "<div class='ghazale_frontend_q_cost' style='display: none'>" . trim(esc_attr($question['question_cost'])) ."</div>" ."<br>";
                    $output .= trim(esc_attr($question['inquiry_question'])) . "<br><br>";

                    $output .= "<div class=\"onoffswitch\">
                       <input type=\"checkbox\" name=\"onoffswitch-q-". $question['id'] ."\" class=\"ghazale-q-onoffswitch-checkbox\" id=\"q-myonoffswitch-". $question['id'] ."\">
                       <label class=\"onoffswitch-label\" for=\"q-myonoffswitch-". $question['id']."\">
                            <span class=\"onoffswitch-inner\"></span>
                            <span class=\"onoffswitch-switch\"></span>
                        </label>
                    </div>
                </div>";

                if(isset($_POST["onoffswitch-q-" . $question['id']])) {
                    array_push($ghazale_inquiry_questions, trim(esc_attr($question['inquiry_question'])));
                    array_push($ghazale_frontend_q_cost, trim(esc_attr($question['question_cost'])));
                }

                if(count($conditions)>0){
                    $ghazale_inquiry_conditions = array();
                    $ghazale_frontend_c_cost = array();

                   $output .= "<div class=\"ghazale_inquiry_conditions\" style=\"display: none\">";

                        foreach($conditions as $condition) {

                            if ($condition['question_id'] == $question['id']) {

                                $output .= "<div class='ghazale_frontend_c_cost' style='display: none;'>" .trim(esc_attr($condition['condition_cost'])) ."</div>" ."<br>";
                                $output .= trim(esc_attr($condition['inquiry_condition'])) . "<br><br>";

                                $output .= "<div class=\"onoffswitch\">
                                    <input type=\"checkbox\" name=\"onoffswitch-c-". $condition['id']."\" class=\"ghazale-c-onoffswitch-checkbox\" id=\"c-myonoffswitch-". $condition['id'] ."\" >
                                    <label class=\"onoffswitch-label\" for=\"c-myonoffswitch-". $condition['id']."\">
                                        <span class=\"onoffswitch-inner\"></span>
                                        <span class=\"onoffswitch-switch\"></span>
                                    </label>
                                </div>";

                            }
                            if(isset($_POST["onoffswitch-c-{$condition['id']}"])) {
                                array_push($ghazale_inquiry_conditions, trim(esc_attr($condition['inquiry_condition'])));
                                array_push($ghazale_frontend_c_cost, trim(esc_attr($condition['condition_cost'])));
                            }
                        }
                    $output .= "</div>";
                }
            $output .= "</div>";
        }

        $output .= "<div>";

            $output .= "<p>Would you like to receive cost breakdown by email? <br>
                <input type=\"checkbox\" name=\"ghazale_inquiry_email_cost_breakdown\" class=\"ghazale_inquiry_email_cost_breakdown\" /> Yes </p>
            <p class=\"ghazale_inquiry_user_email_field\">Email: <input type=\"email\" name=\"ghazale_inquiry_user_email\" id=\"ghazale_inquiry_user_email\" /><br><br><input type=\"submit\" name=\"ghazale_inquiry_submit_user_email\" value=\"Submit\"></p>

        </div>
        </div>
        </div>
        </form>";
    }
    return $output;
}
add_shortcode('inquiry-calc','ghazale_inquiry_frontend_shortcode');

/**
 * emails user and admin
 */

function ghazale_inquiry_email_user_and_admin(){
    global $wpdb;
    $q_table = $wpdb->prefix ."ghazale_inquiry_q";
    $c_table = $wpdb->prefix ."ghazale_inquiry_c";
    $questions_query = "SELECT * FROM {$q_table} ORDER BY id ASC";
    $questions = $wpdb-> get_results($questions_query,ARRAY_A);
    $conditions_query = "SELECT * FROM {$c_table} ORDER BY id ASC";
    $conditions = $wpdb->get_results($conditions_query,ARRAY_A);

    if ($_POST["ghazale_inquiry_submit_user_email"]) {
        if(count($questions)>0) {
            $ghazale_inquiry_questions = array();
            $ghazale_frontend_q_cost = array();
            foreach ($questions as $question) {

                if(isset($_POST["onoffswitch-q-" . $question['id']])) {
                    array_push($ghazale_inquiry_questions, trim(esc_attr($question['inquiry_question'])));
                    array_push($ghazale_frontend_q_cost, trim(esc_attr($question['question_cost'])));
                }

                if(count($conditions)>0){
                    $ghazale_inquiry_conditions = array();
                    $ghazale_frontend_c_cost = array();

                    foreach($conditions as $condition) {

                        if(isset($_POST["onoffswitch-c-{$condition['id']}"])) {
                            array_push($ghazale_inquiry_conditions, trim(esc_attr($condition['inquiry_condition'])));
                            array_push($ghazale_frontend_c_cost, trim(esc_attr($condition['condition_cost'])));
                        }
                    }

                }
            }

            $output = "";
            if (count($ghazale_inquiry_questions) > 0) {

                $output .= "<p>" . "Below is the inquiry cost-breakdown from the online inquiry system: " . "</p>";
                $output .= "<table><tr align='left'><th>Question</th><th>Answer</th><th>Cost</th></tr>";
                $output .= "<tr><td><h4>" . "Main Selections: " . "</h4></td></tr>";

                for ($count = 0; $count <= count($ghazale_inquiry_questions) - 1; $count++) {
                    $output .= "<tr><td>" . $ghazale_inquiry_questions[$count] . "</td><td> YES </td>" . "<td>" . get_option('ghazale_inquiry_currency') . " " . $ghazale_frontend_q_cost[$count] . " </td></tr>";
                }


                if (count($ghazale_inquiry_conditions) > 0) {

                    $output .= "<tr><td><h4>" . "Related Options: " . "</h4></td></tr>";
                    for ($count = 0; $count <= count($ghazale_inquiry_conditions) - 1; $count++) {
                        $output .= "<tr><td>" . $ghazale_inquiry_conditions[$count] . "</td><td> YES </td>" . "<td>" . get_option('ghazale_inquiry_currency') . " " . $ghazale_frontend_c_cost[$count] . " </td></tr>";
                    }

                }


                if (trim(get_option('ghazale_inquiry_min_cost'))) {
                    $output .= "<tr style='border-top: 1px solid #000'><td></td><td><br>Minimum Flat Cost: </td><td><br>" . esc_attr(get_option('ghazale_inquiry_currency')) . " " . esc_attr(get_option('ghazale_inquiry_min_cost')) . "</td></tr>";
                }
                $output .= "<tr style='border-top: 1px solid #000'><td></td><td><strong><br>Total Cost: </strong></td><td><br><strong>" . esc_attr(get_option('ghazale_inquiry_currency')) . " " . (floatval(trim(esc_attr(get_option('ghazale_inquiry_min_cost')))) + array_sum($ghazale_frontend_q_cost) + array_sum($ghazale_frontend_c_cost)) . "</strong></td></tr>";

                $output .= "</table>";
                $output .= home_url();

            }

            if (is_email(trim($_POST["ghazale_inquiry_user_email"]))) {

                $to = trim($_POST["ghazale_inquiry_user_email"]);
                $subject = "Inquiry Cost-Breakdown From " . get_bloginfo('name');
                $message = $output;
                $header = 'From:' . get_bloginfo('name') . ' <';
                if (get_option('ghazale_inquiry_admin_mail')) {
                    $header .= get_option('ghazale_inquiry_admin_mail');
                } else {
                    $header .= get_option('admin_email');
                }
                $header .= '>' . "\r\n" . 'Content-Type: text/html; charset=UTF-8' . "\r\n";
                wp_mail($to, $subject, $message, $header);

                if(get_option('ghazale_inquiry_admin_mail')){
                    $to_admin = get_option('ghazale_inquiry_admin_mail');
                }else{
                    $to_admin = get_option('admin_email');
                }

                $subject_admin = "Your user received inquiry cost-breakdown from you website " . get_bloginfo('name');
                $header_admin = 'Content-Type: text/html; charset=UTF-8' . "\r\n";
                wp_mail($to_admin, $subject_admin, $message , $header_admin);

                if(trim(get_option('ghazale_inquiry_thankyou_url'))){
                    wp_redirect(trim(get_option('ghazale_inquiry_thankyou_url')));
                    exit;
                }else {
                    $_SESSION["message_frontend"] = "Email Sent!";
                }
            } else {
                $_SESSION['message_frontend'] = "Please enter a valid email";
            }
        }

    }
}

add_action('wp_loaded','ghazale_inquiry_email_user_and_admin');



/**
 * enqueue stylesheet and scripts
 */

function ghazale_frontend_style(){
    wp_enqueue_style('ghazale_inquiry_checkbox_style', plugins_url('css/ghazale_inquiry_checkbox_style.css',__FILE__));
    wp_enqueue_script('ghazale_inquiry_organize', plugins_url('js/ghazale_inquiry_organize.js',__FILE__),array('jquery'));
    wp_enqueue_script('ghazale_inquiry_diyslider', plugins_url('js/jquery.diyslider.min.js',__FILE__),array('jquery'));
}
add_action('wp_enqueue_scripts','ghazale_frontend_style');