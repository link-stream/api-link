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
              <div class="row justify-center txt-login-big">Password Reset</div>                                
              <div class="row justify-center enter-email q-mb-xs">Enter the email address you used at sign up and we’ll send you password reset instructions.</div>  
            </q-card-section>                      
        </div>   
        <q-card-section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 card-forgot q-mt-xl">
            <q-form @submit.prevent.stop="onSubmit" autocorrect="off" autocapitalize="off" autocomplete="off" spellcheck="false" class="user-form">    
              <label class="input-label">Email Address</label>  
              <q-input class="col-xs-11 col-sm-10 col-md-10 col-lg-10 col-xl-10 form-input" outlined square bottom-slots  type="email" id="email" name="email"
              color="dark" v-model.trim="$v.email.$model" :error-message="emailErrors" :error="$v.email.$anyError" />    
              <div class="row justify-center">
                <q-btn class="btn-reset" flat type="submit">
                  <div class="txt-reset">Reset</div>
                </q-btn>
              </div>              
            </q-form>
            <div class="q-mt-lg row justify-center already-sign-up">Need help?&nbsp; <a href="#" class="link-login">Contact Us</a></div>
            <div class="row justify-center already-sign-up">Already have an account?&nbsp; <a href="login" class="link-login">Sign in</a></div>
        </q-card-section>  
    </div>
</template>
<script>
import {
  required,
  email
} from "../../../../assets/node_modules/vuelidate/lib/validators";
export default {
   props: {
    baseurl: {
      type: String,
      default: ""
    }
  },
  name: "forgot_password",  
  data() {
    return {
      email: "",
      login: urlBase+'login',
      icon_logo: ''
    };
  },
  validations: {
    email: {
      required,
      required,
      email
    }
  },
  computed: {
    emailErrors() {
      if (!this.$v.email.required) return "* Required";
      if (!this.$v.email.email) return "You must enter a valid email address";
    }
  },
  mounted () {
    document.title = 'LinkStream - Forgot Password';
    this.icon_logo =  `${httpAssets}images/icons/streamy-logo.svg`;
  },
  methods: {    
    onSubmit() {
      this.$v.$touch();
      if (!this.$v.$invalid) {
      }
    }
  }
};
</script>
<style>
</style>