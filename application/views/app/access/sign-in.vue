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
                    <div class="social-btn-txt">Sign in with Instagram</div>
                </q-btn>
                <q-btn align="left" class="col col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 sign-up-with-google" no-caps flat type="a" id="customBtn">
                    <q-icon left class="q-mr-xl icon-google" :name="icon_google" /> 
                    <div class="social-btn-txt q-ml-md" >Sign in with Google</div>
                </q-btn>  
                <div align="center" class="col col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 sign-in-with-email">Or sign in with your email</div>   
            </q-card-section>                      
        </div>
        <q-card-section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 card-form">
          <q-form @submit.prevent.stop="onSubmit" autocorrect="off" autocapitalize="off" autocomplete="off" spellcheck="false" class="user-form">    
            <label class="input-label">Email Address</label>  
            <q-input class="col-xs-11 col-sm-10 col-md-10 col-lg-10 col-xl-10 form-input" outlined square bottom-slots  type="email" id="email" name="email"
            color="dark" v-model.trim="$v.email.$model" :error-message="emailErrors" :error="$v.email.$anyError" />     
            <label class="input-label" >Password</label>
            <q-input class="col-xs-11 col-sm-10 col-md-10 col-lg-10 col-xl-10 form-input" outlined square bg-color="white" type="password" id="password" name="password"
            color="dark" :type="isPwd ? 'password' : 'text'" v-model.trim="$v.password.$model" :error-message="passwordErrors" :error="$v.password.$anyError">
              <template v-slot:append>
                <q-icon
                  :name="isPwd ? 'visibility_off' : 'visibility'"
                  class="cursor-pointer"
                  @click="isPwd = !isPwd"
                />
              </template>
            </q-input>
            <div class="row justify-center">
              <q-btn class="btn-sign-in" flat type="submit">
                <div class="txt-sign-in">Sign in</div>
              </q-btn>                
            </div>
          </q-form>            
        </q-card-section>  
        <div class="down-container">
          <q-btn class="row items-center" flat type="a" :href="forgot" no-caps unelevated dense color="white">
            <div class="forgot-password">Forgot Password?</div>
          </q-btn>
          <div class="q-mt-md row justify-center already-sign-up">Don't have an account?&nbsp; <a href="register" class="link-login">Sign up</a></div>
        </div>  
    </div>
</template>
<script>
import axios from "../../../../assets/node_modules/axios";
import { required, email, minLength } from "../../../../assets/node_modules/vuelidate/lib/validators";
export default {
  name: 'sign_in',  
  data() {
    return {     
      email: '',
      password: '',
      cardClasses: 'self-center my-card midlle',
      signinClass: 'signintxt',   
      isPwd: true,
      login: urlBase+'login',
      forgot: urlBase+'forgot',
      register: urlBase+'register',
      instagram: urlBase+'instagram_register', 
      icon_logo: '', 
      icon_instagram: '',
      icon_google: ''
    };
  },
  validations: {
    email: {
      required,
      email
    },
    password: {
      required,
      minLength: minLength(8)
    }
  },
  computed: {
    emailErrors () {
      if (!this.$v.email.required) return '* Required';
      if (!this.$v.email.email) return 'You must enter a valid email address';
    },
    passwordErrors () {
      if (!this.$v.password.required) return '* Required';
      if (!this.$v.password.minLength) return 'The password must have at least 8 characters';
    }
  },
  mounted () {
    document.title = 'LinkStream - Sign In';
    this.icon_logo =  `${httpAssets}images/icons/streamy-logo.svg`;
    this.icon_instagram =  `img:${httpAssets}images/icons/icon-instagram.svg`;
    this.icon_google =  `img:${httpAssets}images/icons/icon-google.svg`
  },
  methods: {    
    onSubmit () {
      this.$v.$touch();
      if (!this.$v.$anyError) {
        let formData = new FormData();
        formData.append('email', this.email);
        formData.append('password', this.password);
        axios({
          method: 'post',
          url: urlBase + 'app/login_js',
          data: formData,
          config: { headers: {'Content-Type': 'multipart/form-data' }}
        })
        .then(response => {
            if (response.data.status === 'Success') {
              window.location = 'app';
            } else
              this.$q.notify({ message: response.data.msg, color: 'negative', icon: 'error' });
          })
          .catch(function(error) {
            console.log(error);
          });          
      }
    }
  }
}
</script> 
<style>
</style>