<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>Streamy Register</title>
    </head>
    <body>
        <div class="form-field-group">
            <h1>Create Streamy! <?= (!empty($user_name)) ? $user_name : '' ?></h1>
        </div> 
        <br>
        <form action="" method="post" class="form-inline" role="form" id="login" >
            <div class="smart-forms">
                <div class="form-field-group">
                    <div class="form-group">
                        <label class="field">
                            <input type="text" id="url" name="url" class="gui-input ignore xinput" placeholder="" value="" tabindex="1">
                            <label for="url" class="placeholder">URL</label>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="field">
                            <select name="service_type" id="service_type" class="ignore xinput" placeholder="" required tabindex="2" >
                                <option value="1" >SoundCloud</option>
                                <option value="2" >YouTube</option>
                                <option value="3" >LinkStreams</option>
                            </select>
                            <label for="user" class="placeholder">Type</label>
                        </label>
                    </div>
                </div>
                <div id="error"><?= (!empty($msg)) ? '<div class="addr_fail alert alert-danger">' . $msg . '</div>' : '' ?></div>
                <br />
                <button type="submit" class="btn-pill btn-pill-outline-neg btn-lg btn-block">Save</button>
            </div>
        </form> 
        <br>
       

    </body>
</html>
