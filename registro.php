<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            width: 100%;
            gap: 5px;
        }

        .form-group-checkbox {
            flex-direction: row;
            align-self: center;
        }

        input[type="submit"] {
            align-self: center;
            cursor: pointer;
        }

        ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>

    <form action="" method="post">
        <div class="form-group">
            <label for="name">Indica tu nombre:</label>
            <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? $_POST['name'] : ''; ?>"/> 
        </div>
        <div class="form-group">
            <label for="surname">Indica tu apellido:</label>
            <input type="text" id="surname" name="surname" value="<?php echo isset($_POST['surname']) ? $_POST['surname'] : ''; ?>"/> 
        </div>
        <div class="form-group">
            <label for="email">Indica tu correo:</label>
            <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>"/> 
        </div>
        <div class="form-group">
            <label for="fnac">Indica tu fecha de nacimiento:</label>
            <input type="date" id="fnac" name="fnac" value="<?php echo isset($_POST['fnac']) ? $_POST['fnac'] : ''; ?>"/> 
        </div>
        <div class="form-group">
            <label for="password">Indica tu contraseña:</label>
            <input type="password" id="password" name="password" value="<?php echo isset($_POST['password']) ? $_POST['password'] : ''; ?>"/>
        </div>
        <div class="form-group">
            <label for="rPassword">Repite la contraseña:</label>
            <input type="password" id="rPassword" name="rPassword" value="<?php echo isset($_POST['rPassword']) ? $_POST['rPassword'] : ''; ?>"/>
        </div>
        <div class="form-group">
            <label for="gender">Indica tu género:</label>
            <select id="gender" name="gender" required>
                <option value="masculine" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'masculine') ? 'selected' : ''; ?>>Masculino</option>
                <option value="femenine" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'femenine') ? 'selected' : ''; ?>>Femenino</option>
                <option value="others" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'others') ? 'selected' : ''; ?>>Otros</option>
            </select>
        </div>
        <div class="form-group-checkbox">
            <input type="checkbox" id="privacy" name="privacy" value="accepted" <?php echo (isset($_POST['privacy']) && $_POST['privacy'] === 'accepted') ? 'checked' : ''; ?> />
            <label for="privacy">Acepto las políticas de privacidad</label>
        </div>
        <input type="submit" value="Enviar" />
    </form>

    <div>
        <?php
            $error = "";
            $formFields = [
                "name" => "nombre", 
                "surname" => "apellidos", 
                "email" => "correo", 
                "fnac" => "fecha de nacimiento",
                "password" => "contraseña", 
                "rPassword" => "contraseña repetida", 
                "gender" => "género",
                "privacy" => "políticas de privacidad"
            ];
            $formRequired = ["email", "password", "rPassword", "gender", "privacy"];

            foreach ($formRequired as $id) {
                if (!isset($_POST[$id]) || trim($_POST[$id]) === "") {
                    $error .= "Error: no has rellenado o aceptado el/la " . $formFields[$id] . "<br>";
                }
            }

            if (isset($_POST["password"]) && isset($_POST["rPassword"]) && $_POST["password"] !== "" && $_POST["rPassword"] !== "") {
                if ($_POST["rPassword"] !== $_POST["password"]) {
                    $error .= "Error: las contraseñas no corresponden <br>";
                }
            }

            if (isset($_POST["fnac"]) && $_POST["fnac"] !== "") {
                $fechaNacimiento = new DateTime($_POST["fnac"]);
                $hoy = new DateTime();
                $edad = $hoy->diff($fechaNacimiento)->y;

                if ($edad < 14) {
                    $error .= "Error: Debes tener al menos 14 años para registrarte <br>";
                }
            }

            if ($error == "") {
                echo '<ul>';
                foreach ($_POST as $id => $value) {
                    if (isset($formFields[$id])) {
                        echo '<li>' . ucfirst($formFields[$id]) . ": " . $value . '</li>';
                    }
                }
                echo '</ul>';
            } else {
                echo $error;
            }
        ?>
    </div>

</body>
</html>
