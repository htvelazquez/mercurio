/* jshint strict:false */
/* exported SantanderLoginHanlder */
var SantanderLoginHanlder = {
  dni: '35116486',
  user: '1fKhW2G34Vy2cPb',
  pass: '7980',
  init: function (done) {

    // sumTwentyAfterTwoSeconds(10)
    //   .then(result => console.log('after 2 seconds', result));
    //
    // async function sumTwentyAfterTwoSeconds(value) {
    //   const remainder = afterTwoSeconds(20)
    //   return value + await remainder
    // }
    //
    // function afterTwoSeconds(value) {
    //   return new Promise(resolve => {
    //     setTimeout(() => { resolve(value) }, 2000);
    //   });
    // }

    this.setDniValue(this.dni);
    this.setPasswordValue(this.pass);
    this.setUserValue(this.user);
    this.submitForm();
    // done();
  },

  getDniField: function(){
    return jQueryScraper("input[name=dni]");
  },

  setDniValue: function(value){
    var elem = this.getDniField();
    this.setValueIncrementally(elem,value);
  },

  getUserField: function(){
    return jQueryScraper("input[name=usuario]");
  },

  setUserValue: function(value){
    var elem = this.getUserField();
    this.setValueIncrementally(elem,value);
  },

  getPasswordField: function(){
    return jQueryScraper("input[name=clave]");
  },

  setPasswordValue: function(value){
    var elem = this.getPasswordField();
    this.setValueIncrementally(elem,value);
  },

  getSubmitButton: function(){
    return jQueryScraper("button.btn-login[type=submit]");
  },

  getLoginForm: function(){
    return jQueryScraper("form#form");
  },

  submitForm: function(){
    var form = this.getLoginForm();
    form
      .removeClass('ng-pristine ng-invalid ng-invalid-required')
      .addClass('ng-dirty ng-valid-parse ng-valid ng-valid-required');

    var button = this.getSubmitButton();
    button.addClass('enabled').prop("disabled", false); //.click();
  },

  setValueIncrementally: function(elem, value){

    if (elem.val() == value) return; // if the value is already loaded we'll skip this step

    elem.val('');
    elem.focus();
    // elem.val(value);

    for (var i = 0; i<value.length; i++){
        var strChar = value.charAt(i);

        var e = jQueryScraper.Event("keydown");
        e.which = this.getKeyCode(strChar);
        elem.trigger(e);

        elem.val( function( index, val ) {
            return val + strChar;
        });
    }

    elem
      .addClass('ng-valid-maxlength ng-touched ng-not-empty ng-dirty ng-valid-parse ng-valid-required ng-valid ng-valid-minlength')
      .removeClass('ng-pristine ng-untouched ng-empty ng-invalid ng-invalid-required');

    elem.parent()
      .addClass('md-input-has-value')
      .removeClass('md-input-invalid');

    elem.blur();
  },

  getKeyCode: function(char){
    return char.charCodeAt(0);
  }
};
