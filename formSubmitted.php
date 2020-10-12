<!DOCTYPE html>
<html>
    <head>
        <meta charset ="utf-8">
        <title>Form Submitted!</title>
        <style>
            table, td, th {
                border: solid;
                border-width: 2px;
                border-color: lightblue;
            }

            .invisible { display:none; }

            .visible { display: block; }

            .switchlightOn { background-color: white; color:black; }

            .switchlightOut { background-color: black; color:green; }
        </style>

        <script>          
                    var mainBody ;
                    var lightOut ;
                    var lightOn ;

                function outWasClicked()
                {	
                    lightOn.setAttribute("class", "visible");
                    lightOut.setAttribute("class", "invisible");
                    mainBody.setAttribute("class", "switchlightOut");
                }

                function onWasClicked()
                {
                    lightOut.setAttribute("class", "visible");
                    lightOn.setAttribute("class", "invisible");
                    mainBody.setAttribute("class", "switchlightOn");	
                }
                
                function init()
                {
                        mainBody = document.getElementById("mainBody");
                        lightOut = document.getElementById("lightOut");
                        lightOn = document.getElementById("lightOn");
                    lightOut.addEventListener("click", outWasClicked);
                    lightOn.addEventListener("click", onWasClicked);
                }

                window.addEventListener("load", init)
        </script>
    </head>
        <body id="mainBody">
                <?php 
                    //create variables 
                    $username = $_REQUEST["username"];
                    $notAllowedUsernames = array('admin','Admin','administrator','Administrator');
                    $password = $_REQUEST["password"];
                     

                    $returnHomePage = "<a href="."lab2.html".">Return to home page</a>";
                    $mode = $_REQUEST["mode"];

                    // connect to DB
                    $dbhandle = mysqli_connect("localhost", "root", "2kw2ZWKNonZGT0GT", "lab2");
                    if(!$dbhandle) //if connection to DB failed
                    {
                        print("Could not connect to database...");
                        print(mysqli_connect_error());
                        print("</body></html>");
                        die();
                    }

                    //if mode is register -------------------------------------------------------------------------------------------
                    if($mode === "register")
                    {
                        //username query
                        $userNameQuery = "SELECT username FROM users WHERE username LIKE '$username'";
                        $usernameResult = mysqli_query($dbhandle, $userNameQuery);

                        //do not allow user to register administrator account
                        if(in_array($username,$notAllowedUsernames,true))
                        {
                            mysqli_close($dbhandle);
                            print("Sorry, username that has <em><b>admin</b></em> string in it, is not allowed. Please try again.");
                            print("<br><br>");
                            print $returnHomePage;
                            die();
                        }
                        //if username is already taken
                        if(mysqli_num_rows($usernameResult)>0)
                        {
                            mysqli_close($dbhandle);
                            print("Sorry, username <b>".$username."</b> is already taken. Please select different username and try again.");
                            print("<br><br>");
                            print $returnHomePage;
                            die();
                        }
                        //else register new user with hashed password
                        else
                        {
                            if($password != '')
                            {
                                $hash = password_hash($password, PASSWORD_DEFAULT);

                                $sql = "INSERT INTO `users`(`username`, `password`) VALUES (?, ?)";
                                $statement = mysqli_prepare($dbhandle, $sql);
                                $statement->bind_param("ss", $username, $hash);
                                if ( ! $statement->execute() )
                                {
                                    die("stmt did not execute! </body></html>");
                                }
                            }
                            else
                            {
                                print ('<h3>No password was entered. Please try again</h3>');
                                mysqli_close($dbhandle);
                                print("<br><br>");
                                print $returnHomePage;
                                die();

                            }

                            mysqli_close($dbhandle);
                            print("<h1>Welcome ". $username . "!</h1>");
                            print('<h2>Registration successfull!</h2>');
                            print("<br><br>");
                            //print ('Hash = '. $hash . ' is your hash');
                            print $returnHomePage;
                            die();
                        }
                    }

                    //if mode is login-----------------------------------------------------------------------------------------------
                    
                    if( $mode === "login")
                    {
                        //statement for administrator table print
                        $sql = "SELECT id, username, password FROM users";
                        $result = mysqli_query($dbhandle, $sql);

                        //get hashed password from database depending on name entered
                        $loginQuery = " SELECT password FROM users WHERE username LIKE ? ";
                        $preparedStmt = mysqli_prepare($dbhandle, $loginQuery);
                        $preparedStmt->bind_param("s", $username);

                        if ( ! $preparedStmt->execute() )
                        {
                            die("stmt did not execute!!! </body></html>");
                        }

                        $preparedStmt->bind_result($temp);
                        $preparedStmt->fetch();
                        
                        //if entered password matches with stored value in database
                        if (password_verify($password, $temp)) 
                        {
                            // if admin log in print table
                            if ( $username === "admin")
                            {
                                print("<table>
                                            <tbody>
                                                    <tr>
                                                        <th> userID </th>
                                                        <th> userName </th>
                                                        <th> password </th>
                                                    </tr>");
        
                                while($row = mysqli_fetch_row($result)) // fetching results from $sql querry
                                {
                                    print("<tr>
                                                <td>");
                                                        print $row[0];
                                    print("</td>
                                                <td>");
                                                        print $row[1];
                                    print("</td>
                                                <td>");
                                                        print $row[2];
                                        print("</td>
                                    </tr>");
                                }
                        
                                print("</tbody>
                                    </table>");
                            }
                            //if regular user logged in
                            else
                            {
                                    print("<h1>Welcome back " . $username . " .  Glad to see you!</h1>");
                                    print('<br><br>
                                            <input type="button" id="lightOut" value="LIGHTS OUT!" class="visible">
                                    <br><br>
                                            <input type="button" id="lightOn" value="BRING LIGHTS BACK ON!" class="visible">
                                        <br><br>');
                            }
                        }
                        else // if password doesn't match
                            {
                                print("<h2>Incorrect password! Please try again</h2>");
                                print $returnHomePage;
                                mysqli_close($dbhandle);
                                die();  
                            }
                
                }
                else // if password doesn't match
                {
                    print("<h2><em>Password</em> and <em>username</em> do not match! Please try again!</h2>");
                    print $returnHomePage;
                    mysqli_close($dbhandle);
                    die();                                
                }
                    //close connection
                    mysqli_close($dbhandle);

                    //end of php
                ?>
                    <div id="outputDiv">
                            <br>
                            <br>
                            <a href="lab2.html">Return to home page</a>
                    </div>
        </body>
</html>

