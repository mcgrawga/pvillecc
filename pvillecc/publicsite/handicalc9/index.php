<?
include 'functions.php';
DisplayGeneralPublicHeader();
?>	
            <h1>Handicap Calculator</h1>
                    <form action="home.php" method="POST" name="logintable" class="logintable">
                    <table align="center">
                    <tr><td align="left"><b>Email:</b></td><td><input type="text" name="Email"></td></tr>
                    <tr><td align="left"><b>Password:</b></td><td><input type="password" name="Password"></td></tr>
                    <tr><td  align="left"><input type="submit" value="Login" name="LoginUser"></td></tr>
                    </table>
                    </form>
                    <div id="leftlinks">
                        <A  HREF="newuser.php?SendPassword=1">Forgot Password</A><br>
                    </div>
                    <script language="JavaScript">
                        <!--
                        document.logintable.Email.focus()
                        //-->
                    </script>
<?
DisplayCommonFooter();
?>
