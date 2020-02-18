<template>
  <q-card
    class="q-ml-xl self-center midlle" suare flat>
    <div class="row">
      <div class="col ">
        <q-card-section class="q-mt-md q-pb-none row">
          <span class="steps_track_link">STEP {{no_step}}/3</span>
        </q-card-section>
        <q-card-section class="row title_track_link q-pt-xs q-pb-none">
          <span class="title_track_link">Add a video</span>
        </q-card-section>
        <q-card-section class="q-mb-md row subtitle_track_link">
          <span>{{subtitle}}</span>
        </q-card-section>
        <q-card-section class="q-pa-none row" v-show="show_add_track">
          <div v-show="show_upload_track" class=" self-center justify-center">                         
              <VueFileAgent                                  
                ref="fileTrackRef"
                :multiple="false"
                :deletable="false"
                :meta="false"
                :compact="true"
                :accept="'video/*'"                
                :errorText="{
                  type: 'Please select a video',
                }"
                v-model="fileTrack"
                @select="onSelectTrack($event)">  
                <template v-slot:file-preview-new>
                  <div class="col upload_file_track self-center justify-center icon_track_link" key="new">
                    <div class="row self-center justify-center q-mt-xl">
                      <q-icon class="row  justify-center self-center icon_track_link" :name="icon_track" size="98px"/>
                    </div>
                    <div class="row self-center justify-center q-mt-md">
                      <span class="subtitle_icon_track_link">{{text_upload}} 
                        <a href="#" class="subtitle2_icon_track_link">{{browse}}</a>
                      </span>                      
                    </div>
                  </div>
                </template >               
              </VueFileAgent>
              <div class="row q-mt-md" v-show="fileTrack!== null">                  
                <a href="#" class="subtitle2_icon_track_link row" @click="remove_track">{{remove}}</a>  
              </div>                                                  
          </div>
          <!--
          <div class="upload_file_track self-center justify-center" v-show="show_upload_track">                  
            <div class="col q-mt-xs self-center justify-center icon_track_link" >
              <div class="row self-center justify-center q-mt-xl">
                <q-icon class="row  justify-center self-center icon_track_link" :name="icon_track" size="59px"/>
              </div>              
              <div class="row self-center justify-center q-mt-md">
                <span class="subtitle_icon_track_link row justify-center self-center">{{text_upload}}</span>                    
                <span class="subtitle2_icon_track_link row justify-center self-center">{{browse}}</span>   
              </div>                       
            </div>  
            <q-file borderless v-model="upload_track"></q-file>
          </div> 
          <div class="upload_file_track self-center justify-center" v-show="!show_upload_track">  
            <div class="col q-mt-xs self-center justify-center" >
              <div class="row self-center justify-center q-mt-xl q-pt-md">
                <q-card class="player self-center midlle">                
                  <q-card-section class="row items-center no-wrap q-py-none q-px-none  q-mx-xs self-center justify-center">  
                    <q-btn flat color="white" round :icon="icon_player" size="md" @click="change_icon_player"></q-btn>                    
                    <q-slider dark v-model="standard" class="q-mx-sm color_line_player " dense/>
                     <span class="time_player q-mr-md">2:00</span>                  
                  </q-card-section>           
                </q-card> 
              </div>
              <div class="row self-center justify-center q-mt-md">
                <span class="subtitle_icon_track_link row justify-center self-center">{{title_track}}</span>    
                <a href="#" class="subtitle2_icon_track_link row justify-center self-center" @click="remove_track">{{remove}}</a>  
              </div>
            </div>        
          </div>   -->    
        </q-card-section>
        <q-card-section class="q-pa-none row" v-show="show_add_coverArt">
          <div v-show="show_upload_photo" class=" self-center justify-center">                         
              <VueFileAgent                                  
                ref="filePhotoRef"
                :multiple="false"
                :deletable="false"
                :meta="false"
                :compact="true"
                :disabled="publish"
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
              <div class="row q-mt-md" v-show="no_step === 2">                  
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
        <q-card-section class="q-pa-none row " v-show="show_publish">
          <div class="col">  
            <div class="row q-mt-md ">
              <span class="label_publish">Track Name</span>              
            </div> 
            <div class="row q-mt-sm q-mb-lg component_publish">              
              <q-input outlined v-model="publish_name" placeholder="Enter name" class="component_publish" color="dark"/>
            </div> 
            <div class="row q-mt-md ">
              <span class="label_publish">Track Price</span>              
            </div> 
            <div class="row q-mt-sm q-mb-lg">
              <q-input outlined v-model="publish_price" placeholder="$0.00" class="component_publish" color="dark"/>
            </div> 
            <div class="row q-mt-md ">
              <span class="label_publish">Track Type</span>              
            </div> 
            <div class="row q-mt-sm q-mb-lg">
              <q-select outlined v-model="publish_type" :options="options_type"  class="component_publish" color="dark"/>
            </div> 
            <div class="row q-mt-md ">
              <span class="label_publish">Track Genre</span>              
            </div> 
            <div class="row q-mt-sm q-mb-lg">
              <q-select outlined v-model="publish_genre" :options="options_genre"  class="component_publish" color="dark"/>
            </div> 
            <div class="row q-mt-md ">
              <span class="label_publish">Track Priority</span>              
            </div>  
            <div class="row q-mt-sm q-mb-lg">
              <q-select outlined v-model="publish_priority" :options="options_priority"  class="component_publish" color="dark"/>
            </div> 
            <div class="row q-mt-md ">
              <span class="label_publish">Track Visibility</span>              
            </div>  
            <div class="row q-mt-sm q-mb-lg">
              <q-select outlined v-model="publish_visibility" :options="options_visibility"  class="component_publish" color="dark"/>
            </div> 
            <div class="row q-mt-md " v-show="publish_visibility === 'Scheduled'">
              <span class="label_publish">Date</span>              
            </div>  
            <div class="row q-mt-md q-mb-lg" v-show="publish_visibility === 'Scheduled'">              
              <q-input outlined v-model="date" mask="date" :rules="['date']" class="component_publish" color="dark">
                <template v-slot:append>
                  <q-icon name="event" class="cursor-pointer">
                    <q-popup-proxy ref="qDateProxy" >
                      <q-date v-model="date" @input="() => $refs.qDateProxy.hide()" minimal/>
                    </q-popup-proxy>
                  </q-icon>
                </template>
              </q-input>
            </div> 
            <div class="row q-mt-md ">
              <span class="label_publish">Explicit Content?</span>              
            </div>  
            <div class="row q-mt-sm q-mb-lg">
              <q-radio v-model="publish_explicit" val="yes" label="Yes" color="pink" />
              <q-radio v-model="publish_explicit" val="no" label="No" color="pink" /> 
            </div> 
              
          </div>        
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
  name: "videos",  
  data () {
    return {  
      fileTrack: null,    
      filePhoto: null, 
      publish: false,   
      no_step: 1,
      subtitle: '',
      show_upload_track: true,
      text_upload: 'Drag and drop here or',
      browse: 'browse', 
      icon_track: '',
      title_track: '',
      icon_player: 'play_arrow',
      remove:'Remove',
      upload_track: [],
      show_add_track: false,
      show_add_coverArt: false,
      show_publish: false,
      text_upload_cover: 'Drag image here or',
      icon_coverArt: '',      
      show_upload_photo: true,
      urlPhoto: '',      
      image: null,
      upload_photo:[],
      suggestion_photo:true,
      publish_name: '',
      publish_price: '',
      publish_type: 'Select Track Type',
      publish_genre: 'Select Genre',
      publish_priority: 'Select Priority',
      publish_visibility: 'Public',
      publish_explicit: 'yes',
      text_btn_next: 'Next',
      show: false,
      standard: 1,
      options_type: ['Song', 'Beat','Select Track Type'],
      options_genre: ['Select Genre'],
      options_priority: ['Spotlight', 'Normal','Select Priority'],
      options_visibility: ['Public', 'Private', 'Scheduled'],
      date: new Date(),
      slides: [
        {
          text: 'Upload video file',
          visible: true,          
        },
        {
          text: 'Add cover art',
          visible: true,
        },
        {
          text: 'Publish your content',
          visible: true,
        }        
      ],

    }
  },
  mounted () {        
    this.icon_track =  `img:${httpAssets}images/icons/icon-cloud-upload.svg`;
    this.icon_coverArt =  `img:${httpAssets}images/icons/ic-outline-add-a-photo.svg`; 
    console.log(this.icon_track)
    document.title = 'LinkStream - Add Track'
    this.subtitle = this.slides[this.no_step - 1].text
    this.show_add_track = this.slides[this.no_step - 1].visible
    
  },
  watch: {   
   
     no_step (){
      this.text_btn_next = (this.no_step === 3) ? 'Publish' : 'Next'
      this.publish = this.no_step === 3
      //this.subtitle = this.list_subtitle[this.no_step - 1]
    },
    upload_track(){
      if (this.upload_track.length !== 0){
        this.title_track = this.upload_track.name
        this.show_upload_track = false
      }
      console.log(this.upload_track)
      console.log(this.upload_track.name)
    },
    upload_photo(){
      if (this.upload_photo.length !== 0){ 
        this.suggestion_photo = false       
        this.load_photo (event)
        //this.show_upload_track = false
      }
      console.log(this.upload_photo)
      //console.log(this.upload_track.name)
    }

  },  
  methods: {
    change_icon_player(){
      this.icon_player = (this.icon_player !== 'pause') ? 'pause' : 'play_arrow'
    },
    nextStep (){
      if(this.no_step < 3){
        this.no_step ++;
        this.subtitle = this.slides[this.no_step - 1].text
        switch(this.no_step){
          case 1: {
            this.show_add_track = this.slides[this.no_step - 1].visible;
            this.show_add_coverArt = false;
            this.show_publish = false;
            } break;
          case 2: {
            this.show_add_coverArt = this.slides[this.no_step - 1].visible; 
            this.show_add_track = false;
            this.show_publish = false;
          }break;
          case 3: {
            this.show_publish = this.slides[this.no_step - 1].visible;            
            this.show_add_track = false;
            this.show_add_coverArt = true;
            
          }  break;
        }
      } 
    },
    backStep (){
      if(this.no_step > 1){
        this.no_step --;
        this.subtitle = this.slides[this.no_step - 1].text
        switch(this.no_step){
          case 1: {
            this.show_add_track = this.slides[this.no_step - 1].visible;
            this.show_add_coverArt = false;
            this.show_publish = false;
            } break;
          case 2: {
            this.show_add_coverArt = this.slides[this.no_step - 1].visible; 
            this.show_add_track = false;
            this.show_publish = false;
          }break;
          case 3: {
            this.show_publish = this.slides[this.no_step - 1].visible;
            this.show_add_track = false;
            this.show_add_coverArt = true;
          }  break;
        }
      } 
    },
    remove_track () {
      this.fileTrack = null
      this.show_upload_track = true
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
    
    
     
      onSelectTrack: function(filesData){},
      onSelectPhoto: function(filesData){}

  },
  created () {

  }
}
</script> 
<style>



</style>