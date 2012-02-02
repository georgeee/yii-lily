jQuery(function($) {
    
    $('#loginForm').ready(function(){
        $('#loginForm .authMethodSelectDiv').css('display', 'none')
        $('#loginForm .authMethodSwitcherDiv').css('display', 'block')
        $('#loginForm .authMethodSwitcherDiv .authMethod').click(function(){
            $('#loginForm .authMethodSwitcherDiv .authMethod').removeClass('selected');
            $(this).addClass('selected')
            if($(this).hasClass('email')){
                $('#loginForm .emailFieldsDiv').css('display', 'block')
            }else{
                $('#loginForm .emailFieldsDiv').css('display', 'none')
            }
            $('#loginForm .authMethodSelect .option_'+$(this).attr('service')).attr('selected','selected')
            return false;
        })
        $('#loginForm .authMethodSwitcherDiv .authMethod.'+$('#loginForm .authMethodSelect').val()).click()
        $('#login-form').submit(function(event){
            var service = $('#loginForm .authMethodSelect').val();
            if(service == 'email'){
                
            }else{
                event.preventDefault();
                var a = $('#loginForm .auth-service.'+service+' a');
                var href = a.attr('href');
                var rememberMe = $('#loginForm .rememberMeField').val();
                var found = false;
                var sp = href.split('?');
                if(sp.length==1) href = href + "?rememberMe="+rememberMe;
                else{
                    href = sp[0];                
                    var sp2 = sp[1].split('&');
                    for(var i=0; i<sp2.length; i++){
                        var sp3 = sp2[i].split('=');
                        if(sp3[0]=='rememberMe'){
                            sp3[1] = rememberMe;
                            found = true;
                        }
                        href = href + (i==0?'?':'&') + sp3[0] + (sp3.length==1?'':'='+sp3[1]);
                    }
                    if(!found) href = href + '&rememberMe='+rememberMe;
                }
                a.attr('href', href);
                a.click();
                return false;
            }
        })
    })
});
