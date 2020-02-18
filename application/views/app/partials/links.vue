<template>
  <q-card
    class="q-ml-xl self-center midlle" suare flat>
    <div class="row">
      <div class="col ">
        <q-card-section class="q-mt-md q-pb-none row">
          <span class="steps_track_link">STEP {{no_step}}/2</span>
        </q-card-section>
        <q-card-section class="row title_track_link q-pt-xs q-pb-none">
          <span class="title_track_link">Add a link</span>
        </q-card-section>
        <q-card-section class="q-mb-none row subtitle_track_link">
          <span>{{subtitle}}</span>
        </q-card-section>
        <q-card-section class="q-pa-none row" v-show="show_add_link">
          <div  class=" self-center justify-center col">  
            <div class="row q-mt-md ">
              <span class="label_publish">Link Title</span>              
            </div> 
            <div class="row q-mt-sm q-mb-lg component_publish">              
              <q-input outlined v-model="link_title" placeholder="Enter title" class="component_publish" color="dark"/>
            </div> 
            <div class="row q-mt-md ">
              <span class="label_publish">Link Url</span>              
            </div> 
            <div class="row q-mt-sm q-mb-lg">
              <q-input outlined v-model="link_url" placeholder="" class="component_publish" color="dark"/>
            </div> 
          </div>   
        </q-card-section>
        <q-card-section class="q-pa-none row" v-show="show_add_coverArt">
          <div v-show="show_upload_photo" class=" self-center justify-center">                         
              <VueFileAgent                                  
                ref="filePhotoRef"
                :multiple="false"
                :deletable="false"
                :meta="false"
                :compact="true"               
                :accept="'image/*'"                
                :errorText="{
                  type: 'Please select an image',
                }"
                v-model="filePhoto"
                @select="onSelectPhoto($event)">  
                <template v-slot:file-preview-new>
                  <div class="col upload_file_photo self-center justify-center icon_track_link" key="new">
                    <div class="row self-center justify-center q-mt-xl">
                      <q-icon class="row  justify-center self-center icon_track_link" :name="icon_coverArt" size="59px"/>
                    </div>
                    <div class="row self-center justify-center q-mt-md">
                      <span class="subtitle_icon_track_link">{{text_upload_cover}} 
                        <a href="#" class="subtitle2_icon_track_link">{{browse}}</a>
                      </span>                      
                    </div>
                  </div>
                </template >               
              </VueFileAgent>
              <div class="row q-mt-md" >                  
               <span class="suggest_dimension " v-show="filePhoto === null ">Suggested Dimensions: 1000x1000</span>
                <a class="suggest_dimension_remove" v-show="filePhoto !== null" href="#" @click="remove_photo">{{remove}}</a> 
              </div>                                                  
          </div>
          <!--
          <div class="upload_file_photo self-center justify-center" v-show="show_upload_photo">            
            <div class="col q-mt-none self-center justify-center icon_track_link" >
              <div class="row self-center justify-center q-mt-xl">
                <q-icon class="row  justify-center self-center icon_track_link" :name="icon_coverArt" size="59px" v-on:click="add_foto"/>
              </div>              
              <div class="row self-center justify-center">
                <span class="subtitle_icon_track_link row justify-center self-center">{{text_upload_cover}}</span>                    
                <span class="subtitle2_icon_track_link row justify-center self-center q-ml-xsm">{{browse}}</span>   
              </div>                       
            </div>  
            <q-file borderless v-model="upload_photo" v-on:change="load_photo" ref="fileInput"></q-file>
          </div>            
          <div class="col" v-show="!show_upload_photo">
            <div class="upload_file_photo self-center justify-center q-py-none row" >  
               <q-img id="imgElem" :src="urlPhoto" style="height: 220px; max-width: 220px" class="q-py-none"></q-img>                      
            </div> 
          </div>              
        </q-card-section>
        <q-card-section class="q-pl-none q-pt-none" v-show="show_add_coverArt">
          <div class="col q-pl-none q-mt-xs">
            <span class="suggest_dimension " v-show="suggestion_photo">Suggested Dimensions: 1000x1000</span>
            <a class="suggest_dimension_remove" v-show="!suggestion_photo" href="#" @click="remove_photo">{{remove}}</a> 
          </div>  -->        
        </q-card-section>        
        <q-card-section class="row q-pt-none q-pl-none">
          <q-btn class="q-mt-lg q-mr-md back_btn_track_link" flat @click="backStep" v-show="no_step !== 1">
            <div class="back_text_track_link">Back</div>
          </q-btn>
          <q-btn class="q-mt-lg next_btn_track_link" flat @click="nextStep">
            <div class="next_text_track_link">{{text_btn_next}}</div>
          </q-btn>
        </q-card-section>
      </div>
    </div>
  </q-card>
</template>
<script>    
import axios from '../../../../assets/node_modules/axios';
import { required, email, minLength } from '../../../../assets/node_modules/vuelidate/lib/validators';

export default {
  name: "links",  
  data () {
    return {           
      filePhoto: null, 
      show_add_link: true,       
      no_step: 1,
      subtitle: '',     
      browse: 'browse',       
      show_add_coverArt: false,
      show_publish: false,
      text_upload_cover: 'Drag image here or',
      icon_coverArt: '',      
      show_upload_photo: true,
      urlPhoto: '',      
      image: null,
      upload_photo:[],
      suggestion_photo:true,     
      text_btn_next: 'Next',
      show: false,
      standard: 1,    
      link_title: '', 
      link_url: '', 
      remove:'Remove',
      slides: [
        {
          text: 'Add link',
          visible: true,          
        },
        {
          text: 'Add cover art',
          visible: true,
        }     
      ],

    }
  },
  mounted () {
    this.icon_coverArt =  `img:${httpAssets}images/icons/ic-outline-add-a-photo.svg`; 
    document.title = 'LinkStream - Add Link'
    this.subtitle = this.slides[this.no_step - 1].text
    this.show_add_link = this.slides[this.no_step - 1].visible
    
  },
  watch: {   
   
     no_step (){
      this.text_btn_next = (this.no_step === 2) ? 'Publish' : 'Next'
      
    },    
    upload_photo(){
      if (this.upload_photo.length !== 0){ 
        this.suggestion_photo = false       
        this.load_photo (event)       
      }
      console.log(this.upload_photo)
      //console.log(this.upload_track.name)
    }

  },  
  methods: {   
    nextStep (){
      if(this.no_step < 2){
        this.no_step ++;
        this.subtitle = this.slides[this.no_step - 1].text
        switch(this.no_step){
          case 1: {
            this.show_add_link = this.slides[this.no_step - 1].visible;
            this.show_add_coverArt = false;
            this.show_publish = false;
            } break;
          case 2: {
            this.show_add_coverArt = this.slides[this.no_step - 1].visible; 
            this.show_add_link = false;
            this.show_publish = false;
          }break;          
        }
      } 
    },
    backStep (){
      if(this.no_step > 1){
        this.no_step --;
        this.subtitle = this.slides[this.no_step - 1].text
        switch(this.no_step){
          case 1: {
            this.show_add_link = this.slides[this.no_step - 1].visible;
            this.show_add_coverArt = false;
            this.show_publish = false;
            } break;
          case 2: {
            this.show_add_coverArt = this.slides[this.no_step - 1].visible; 
            this.show_add_link = false;
            this.show_publish = false;
          }break;         
        }
      } 
    },   
    remove_photo () {
      this.filePhoto = null
      this.show_upload_photo = true
    },
    add_foto() {
      this.$refs.fileInput.click()
    },


    load_photo(event) {
      const files = event.target.files;                
      let filename = files[0].name;
      if (filename.lastIndexOf('.') <= 0) {
          console.log('por favor.....')
      }
      const fileReader = new FileReader();
      fileReader.addEventListener('load', () => {
          this.urlPhoto = fileReader.result;
          console.log('urlPhoto', this.urlPhoto)
      })
      fileReader.readAsDataURL(files[0]);
      this.image = files[0];
      this.show_upload_photo = false;
      console.log('image', this.image)
    },
    
    
     
     
      onSelectPhoto: function(filesData){}

  },
  created () {

  }
}
</script> 
<style>



</style>