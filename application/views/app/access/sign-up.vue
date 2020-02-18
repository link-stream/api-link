<template>
    <div>
        <div class="top-nav">
            <q-card-section class="q-ma-none q-pa-none logo-linkstream">            
                <img :src="icon_logo" alt="logo" class="streamy-icon">
                <span class="linkstream">LINKSTREAM</span>
            </q-card-section>            
        </div>
        <div class="container">
            <q-card-section class="row justify-center social-sign-up-options">                      
                <q-btn align="left" class="col col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 rectangle" no-caps flat type="a" :href="instagram">
                    <q-icon left class="q-mr-xl icon-instagram" :name="icon_instagram" />
                    <div class="social-btn-txt">Sign up with Instagram</div>
                </q-btn>
                <q-btn align="left" class="col col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 sign-up-with-google" no-caps flat type="a" id="customBtn">
                    <q-icon left class="q-mr-xl icon-google" :name="icon_google" /> 
                    <div class="social-btn-txt q-ml-md">Sign up with Google</div>
                </q-btn>  
                <div align="center" class="col col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 sign-in-with-email">Or sign up with your email</div>   
            </q-card-section>                      
        </div>
        <q-card-section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 card-form">
            <q-form autocorrect="off" autocapitalize="off" autocomplete="off" spellcheck="false" class="user-form">    
              <label class="input-label">Username</label>  
              <q-input class="col-xs-11 col-sm-10 col-md-10 col-lg-10 col-xl-10 form-input" outlined square bottom-slots  type="email" id="email" name="email"
              color="dark" v-model.trim="$v.username.$model" :error-message="usernameErrors" :error="$v.username.$anyError" 
              @blur="evt => onChangedUsername(evt.target.value)" :loading="loadingUsername"/> 
              <label class="input-label">Email</label>  
              <q-input class="col-xs-11 col-sm-10 col-md-10 col-lg-10 col-xl-10 form-input" outlined square bottom-slots  type="email" id="email" name="email"
              color="dark" v-model.trim="$v.email.$model" :error-message="emailErrors" :error="$v.email.$anyError"  
              @blur="evt => onChangedEmail(evt.target.value)" :loading="loadingEmail"/>     
              <label class="input-label">Password</label>
              <q-input class="col-xs-11 col-sm-10 col-md-10 col-lg-10 col-xl-10 form-input" outlined square bg-color="white" type="password" id="password" name="password"
              color="dark" v-model.trim="$v.password.$model" :error-message="passwordErrors" :error="$v.password.$anyError">
                <q-tooltip max-width="250px" content-style="font-size: 12px">Must contain upper and lower case letters, numbers and special characters (@#$%&) </q-tooltip>
              </q-input>
              <label class="input-label">Retype Password</label>
              <q-input class="col-xs-11 col-sm-10 col-md-10 col-lg-10 col-xl-10 form-input" outlined square bg-color="white" type="password" id="repassword" name="repassword"
              color="dark" v-model.trim="$v.repassword.$model" :error-message="repasswordErrors" :error="$v.repassword.$anyError"/>              
              <div class="row justify-center sign-up-terms">By clicking Sign Up below, you agree to our <a href="legal" target="_blank" class="link-login">Terms of Use</a> &nbsp;and&nbsp;<a href="legal" target="_blank" class="link-login"> Privacy Policy</a>.</div>
              <div class="row justify-center">
                <q-btn class="btn-sign-in" type="submit" flat @click.prevent.stop="onSubmit">
                  <div class="txt-sign-in">Sign up</div>
                </q-btn>                
              </div>
              <div class="q-mt-lg row justify-center already-sign-up">Already have an account?&nbsp; <a href="login" class="link-login">Sign in</a></div>
            </q-form>
        </q-card-section>  
    </div>
</template>
<script>
import axios from "../../../../assets/node_modules/axios";
import { required, email, sameAs, not, minLength } from "../../../../assets/node_modules/vuelidate/lib/validators";
export default {
  props: {
    baseurl: {
      type: String,
      default: ""
    }
  },
  name: "sign_up",
  data() {
    return {
      username: '',
      email: '',
      password: '',
      repassword: '',
      bdUsername: '',
      bdEmail: '',
      login: urlBase+'login',
      index: urlBase+'index',
      forgot: urlBase+'forgot',
      legal: urlBase+'legal',
      register: urlBase+'register',
      instagram: urlBase+'instagram_register', 
      icon_logo: '', 
      icon_instagram: '',
      icon_google: '',
      loading: false,
      loadingUsername: false,
      loadingEmail: false
    };
  },
  validations: {    
    username: {
      required,
      minLength: minLength(5),
      sameAsUsername: not(sameAs(function() { return this.bdUsername })),
      isUsernameValid(username) {
        const regex = /^[a-zA-Z0-9_]*$/;
        return regex.test(username);
      }
    },
    email: {
      required,
      email,
      sameAsEmail: not(sameAs(function() { return this.bdEmail}))
    },
    password: {
      required,
      minLength: minLength(8),
      isPasswordValid(password) {
        const regex = /^.*(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%&]).*$/;
        return regex.test(password);
      }
    },
    repassword: {
      required,
      sameAsPassword: sameAs("password")
    }
  },
  computed: {
    usernameErrors() {
      if (!this.$v.username.required) return "* Required";
      if (!this.$v.username.minLength) return "The username must have at least 5 characters";
      if (!this.$v.username.sameAsUsername) return "The username is already in use";
      if (!this.$v.username.isUsernameValid) return "Only accept alphanumeric characters and _";
    },
    emailErrors() {
      if (!this.$v.email.required) return "* Required";
      if (!this.$v.email.email) return "You must enter a valid email address";
      if (!this.$v.email.sameAsEmail) return "The email is already in use";
    },
    passwordErrors() {
      if (!this.$v.password.required) return "* Required";
      if (!this.$v.password.minLength) return "The password must have at least 8 characters";
      if (!this.$v.password.isPasswordValid) return "Invalid format";
    },
    repasswordErrors() {
      if (!this.$v.repassword.required) return "* Required";
      if (!this.$v.repassword.repassword) return "The password does not match";
    }
  },
  mounted () {
    document.title = 'LinkStream - Sign Up',
    this.icon_logo =  `${httpAssets}images/icons/streamy-logo.svg`;
    this.icon_instagram =  `img:${httpAssets}images/icons/icon-instagram.svg`;
    this.icon_google =  `img:${httpAssets}images/icons/icon-google.svg`
  },
  methods: {    
    onChangedUsername(value) {      
      if (value.length > 5) {
        this.loadingUsername = true;
        let formData = new FormData();
        formData.append('username', this.username);
        axios({
          method: 'post',
          url: this.baseurl + "app/verify_username",
          data: formData,
          config: { headers: {'Content-Type': 'multipart/form-data' }}
        })        
        .then(response => {
          if (response.data === false) {
            this.bdUsername = value;
          } else {
            this.bdUsername = "";
          }
          this.loadingUsername = false
        })
        .catch(function(error) {
          console.log(error);
          this.loadingUsername = false
        });        
      }    
    },
    onChangedEmail(value) {
      this.loadingEmail =  true;
      let formData = new FormData();
      formData.append('email', this.email);
      axios({
          method: 'post',
          url: this.baseurl + "app/verify_email",
          data: formData,
          config: { headers: {'Content-Type': 'multipart/form-data' }}
        }) 
        .then(response => {
          if (response.data === false) {
            this.bdEmail = value;
          } else {
            this.bdEmail = "";
          }
          this.loadingEmail =  false;
        })
        .catch(function(error) {
          console.log(error);
          this.loadingEmail =  false
        })
    },
    onSubmit() {
      this.$v.$touch();
      this.$v.email.$touch();
      this.$v.username.$touch();
      if (!this.$v.$invalid) {
        this.loading = true;
        let formData = new FormData();
        formData.append('username', this.username);
        formData.append('email', this.email);
        formData.append('password', this.password);
        formData.append('repassword', this.repassword);
        axios({
          method: 'post',
          url: urlBase + "app/register_js",
          data: formData,
          config: { headers: {'Content-Type': 'multipart/form-data' }}
        })
        .then(response => {
            if (response.data.status === "Success") {
              window.location = "register_confirm";
            } else
              this.$q.notify({ message: response.data.msg, color: "negative", icon: "error" });
            this.loading = false;
          })
          .catch(function(error) {
            console.log(error);
            this.loading = false;
          })         
      }
    }
  }
}
</script> 
<style>
</style>

