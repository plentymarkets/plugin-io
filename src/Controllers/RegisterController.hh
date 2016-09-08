<?hh //strict
namespace LayoutCore\Controllers;

use LayoutCore\Helper\TemplateContainer;

class RegisterController extends LayoutController
{
    public function showRegister(): string
    {
        return $this->renderTemplate(
            "tpl.register",
            array(
                "register" => ""
            )
        );
    }
}
