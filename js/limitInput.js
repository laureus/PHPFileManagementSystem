/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function limitInput(elm){
                var text = elm.value;
                // \/:*?"<>|
                var pattern = /(\\)|(\/)|(:)|(\*)|(\?)|"|(\<)|(\>)|(\|)/g;
                var result = text.match(pattern);

                // prevent backspace
                if(event.keyCode == 8){
                    return false;
                }else{
                    if(result != null){
                    var quote = '"';
                        alert("The following characters are not accepted:  \\ / : * ? " + quote + " < > |");
                        elm.value=elm.value.replace(pattern,"");
                        return true;
                    }
                    return false;
                }
            }

