jQuery(function($) {
    
    $.fn.lily = function() {
        return this.each(function() {
            var el = $(this);
            el.ready(function(){
                el.find('.authMethodSelectDiv, p.emailFieldHint').css('display', 'none')
                el.find('.authMethodSwitcherDiv').css('display', 'block')
                el.find('.authMethodSwitcherDiv .authMethod').click(function(){
                    el.find('.authMethodSwitcherDiv .authMethod').removeClass('selected');
                    $(this).addClass('selected')
                    if($(this).hasClass('email')){
                        el.find('.emailFieldsDiv').css('display', 'block')
                    }else{
                        el.find('.emailFieldsDiv').css('display', 'none')
                    }
                    el.find('.authMethodSelect option').removeAttr('selected')
                    el.find('.authMethodSelect .option_'+$(this).attr('service')).attr('selected','selected')
                    return false;
                })
                el.find('.authMethodSwitcherDiv .authMethod.'+el.find('.authMethodSelect').val()).click()
                el.submit(function(event){
                    var service = el.find('.authMethodSelect').val();
                    if(service == 'email'){
                
                    }else{
                        event.preventDefault();
                        var a = el.find('.auth-service.'+service+' a');
                        var href = a.attr('href');
                        var rememberMe = el.find('.authMethodRememberMe').is(':checked')?1:0;
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
    };
    
    
});
