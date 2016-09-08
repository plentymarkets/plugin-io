<?hh //strict
namespace LayoutCore\Controllers;

use LayoutCore\Helper\TemplateContainer;

class LoginController extends LayoutController
{
    public function showLogin(): string
    {
        return $this->renderTemplate(
            "tpl.login",
            array(
                "login" => ""
            )
        );
    }
}
