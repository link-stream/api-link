<html>
    <head>
        <meta charset="UTF-8">
        <title>Streamy - Your Streams Come True</title>
    </head>
    <body style="background:#000;color:#fff;">
        <table width="500" border="0" align="center">
            <tr>
                <td align="center">
                    <img src="https://link-dev.link.stream/assets/images/logo/streamy_icon_RGB.png" width="180px" alt="streamy logo">
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td style="font-family: arial"><strong>Making Your Streams Come True</strong></td>
            </tr>
            <tr>
                <td><hr></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td style="font-family: arial">Welcome to Streamy <?= (!empty($user) ? $user : '') ?>, Thanks for registering </td>
            </tr>
            <tr>
                <td style="font-family: arial">&nbsp;Please confirm your email <?= (!empty($email) ? $email : '') ?> in the link below</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="btn" style="font-family: sans-serif; font-size: 16px; vertical-align: top; line-height: 1.5em; text-align: center; box-sizing: border-box; background-color: #4aa3ed; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px; width: 100%;" width="100%" valign="top" align="center" bgcolor="#4aa3ed">
                    <a href="<?= (!empty($url)) ? $url : 'https://www.link.stream/' ?>" target="_blank" style="font-size: 14px; background-color: #4aa3ed; border: solid 1px #4aa3ed; text-align: center; font-weight: bold; color: #fff; box-sizing: border-box; cursor: pointer; padding: 12px 30px; text-decoration: none; display: block; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px; text-rendering: optimizeLegibility;">
                        Confirm your Email
                    </a>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td><p style="font-family: arial"><strong>&#x1F496; Streamy</strong></p></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
        </table>
    </body>
</html>