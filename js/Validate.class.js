/**
   javascript validation class
   written by Tracy Lauren
   for The Job Route Corporation March 17, 2010
 **/
var Validate = new Object();

Validate.digits = "0123456789";
Validate.UpperCaseLetters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
Validate.LowerCaseLetters = "abcdefghijklmnopqrstuvwxyz";
Validate.phoneNumberDelimiters = "()-+ ";
Validate.symbolsInNames = "-' .";
Validate.symbolsInUsernames = "-.@_";
Validate.symbolsInAddress = "- #.'";

// data Massaging tools

Validate.stripCharsInArray = function(s, bag)
{
   var i;
    var returnString = "";
    // Search through string's characters one by one.
    // If character is not in bag, append to returnString.
    for (i = 0; i < s.length; i++)
    {
        // Check that current character isn't whitespace.
        var c = s.charAt(i);
        if (bag.indexOf(c) == -1) returnString += c;
    }
    return returnString;
};

Validate.isInteger = function(s)
{   var i;
    for (i = 0; i < s.length; i++)
    {
        // Check that current character is number.
        var c = s.charAt(i);
        if (((c < "0") || (c > "9"))) return false;
    }
    // All characters are numbers.
    return true;
};

Validate.trim = function(s)
{   var i;
    var returnString = "";
    // Search through string's characters one by one.
    // If character is not a whitespace, append to returnString.
    for (i = 0; i < s.length; i++)
    {
        // Check that current character isn't whitespace.
        var c = s.charAt(i);
        if (c != " ") returnString += c;
    }
    return returnString;
};


// field Validatiion

Validate.telephone = function(strPhone)
{
   if(strPhone === "") return false;
   var minDigitsInPhoneNumber = 10;
   var s = this.stripCharsInArray(strPhone, this.phoneNumberDelimiters);

   return (this.isInteger(s) && s.length >= minDigitsInPhoneNumber);
};
Validate.name = function(strName)
{
   if(strName === "") return false;
   var minLettersInName = 2;
   if(strName.length < minLettersInName)
      return false;

   var s = this.stripCharsInArray(strName, this.UpperCaseLetters);
   s = this.stripCharsInArray(s, this.LowerCaseLetters);
   s = this.stripCharsInArray(s, this.symbolsInNames);

   return (s.length === 0);
};
Validate.firstName = function(strName)
{
   if(strName === "") return false;
   var minLettersInName = 2;
   if(strName.length < minLettersInName)
      return false;

   var s = this.stripCharsInArray(strName, this.UpperCaseLetters);
   s = this.stripCharsInArray(s, this.LowerCaseLetters);
   s = this.stripCharsInArray(s, this.symbolsInNames);

   return (s.length === 0);
};
Validate.lastName = function(strName)
{
   if(strName === "") return false;
   var minLettersInName = 2;
   if(strName.length < minLettersInName)
      return false;

   var s = this.stripCharsInArray(strName, this.UpperCaseLetters);
   s = this.stripCharsInArray(s, this.LowerCaseLetters);
   s = this.stripCharsInArray(s, this.symbolsInNames);

   return (s.length === 0);
};
Validate.username = function(strUser)
{
   if(strUser === "") return false;
   var minLettersInName = 4;
   if(strUser.length < minLettersInName)
      return false;

   var s = this.stripCharsInArray(strUser, this.UpperCaseLetters);
   s = this.stripCharsInArray(s, this.LowerCaseLetters);
   s = this.stripCharsInArray(s, this.symbolsInUsernames);
   s = this.stripCharsInArray(s, this.digits);
   return (s.length === 0);
};
Validate.address = function(strAddr)
{
   if(strAddr === "") return false;
   var minLettersInName = 5;
   if(strAddr.length < minLettersInName)
      return false;

   var s = this.stripCharsInArray(strAddr, this.UpperCaseLetters);
   s = this.stripCharsInArray(s, this.LowerCaseLetters);
   s = this.stripCharsInArray(s, this.digits);
   s = this.stripCharsInArray(s, this.symbolsInAddress);
   return (s.length === 0);
};
Validate.password = function(strPass)
{
   if(strPass === "") return false;
   var minLettersInPass = 5;
   if(strPass.length < minLettersInPass)
      return false;
   else
      return true
};
Validate.confirm = function(strPass, strConf)
{
   if(strConf === "") return false;
   if(strConf == strPass) return true;
   else return false;
};

Validate.email = function(strEmail)
{
   var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
   return pattern.test(strEmail);
};
Validate.postalcode = function(strPostal)
{
   if(strPostal === "") return false;
   var s = this.stripCharsInArray(strPostal, this.UpperCaseLetters);
   s = this.stripCharsInArray(s, this.LowerCaseLetters);
   s = this.stripCharsInArray(s, this.digits);
   s = this.stripCharsInArray(s, " -");

   return (s.length === 0);
};

Validate.required = function(field)
{
   var fieldtype = field.type;
   alert(fieldtype);
   switch(fieldtype)
   {
      case 'text':
      case 'textarea':
      case 'select':
      case 'hidden':
      if( field.value === "" )
      {
         alert("FALSE: " +field.value);
                        return false;
      }
      else
         {
            alert("TRUE: " +field.value);
                        return true;
        }
                   break;
      case 'radio': if(field.value != 'on')
                        return true;
                    else
                          return false;
      case 'checkbox': return field.checked; break;
      default: return false; break;
   }
   return false;
};

