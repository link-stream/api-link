import Vue from "vue";
require("../../../assets/css/streamy.css");

import Quasar from "quasar";
import axios from "axios";
import Vuelidate from "vuelidate";
import { mixin as clickaway } from "vue-clickaway";
import VueFileAgent from "vue-file-agent";
import VueFileAgentStyles from "vue-file-agent/dist/vue-file-agent.css";

import sign_in from "../../../application/views/app/access/sign-in";
import sign_up from "../../../application/views/app/access/sign-up";
import forgot_password from "../../../application/views/app/access/forgot-pass";
import confirm_email from "../../../application/views/app/access/confirm-email";
import tracks from "../../../application/views/app/partials/tracks";
import links from "../../../application/views/app/partials/links";
import videos from "../../../application/views/app/partials/videos";
//import dashboard from "../../../application/views/app/partials/dashboard";

Vue.use(Quasar);
Vue.use(Vuelidate);
Vue.use(VueFileAgent);

window._ = require("lodash");
Vue.prototype.$http = axios;

let app = new Vue({
  el: "#q-app",
  mixins: [clickaway],
  props: [],
  components: {
    sign_in,
    sign_up,
    forgot_password,
    confirm_email,
    tracks,
    links,
    videos,
    //dashboard
  },
  Quasar,
  data: function() {
    return {
      size: window.screen.height,
      version: Quasar.version,
      drawerState: true,
      left: false,
      focus: true,
      dynamicWidth: 128,
      menuState: "",
      submenu: false,
      selectedMenu: "Dashboard",
      submenuList: [],     
      menuList: [
        {          
          icon: `img:${httpAssets}images/icons/tachometer-alt.svg`,
          label: "Dashboard",
          linkTo: "#",
          submenu: []
        },
        {          
          icon: `img:${httpAssets}images/icons/icon-user-circle.svg`,
          label: "Profile",
          linkTo: "#",
          submenu: [
            { linkTo: "#", label: "Edit Profile" },
            { linkTo: "#", label: "View Profile" }
          ]
        },
        {          
          icon: `img:${httpAssets}images/icons/icon-tracks.svg`,
          label: "Tracks",
          linkTo: "#",
          submenu: [
            { linkTo: "/link.stream/app/stream", label: "Add Track" },
            { linkTo: "#", label: "Manage Tracks" },
            { linkTo: "#", label: "Promote" }
          ]
        },
        /*{
          icon: "img:assets/images/icons/icon-user-circle.svg",
          label: "My Content",
          linkTo: "#",
          submenu: [
            { linkTo: "#", label: "Add Content" },
            { linkTo: "#", label: "Manage Content" }
          ]
        },*/
        {          
          icon: `img:${httpAssets}images/icons/icon-link.svg`,
          label: "Links",
          linkTo: "#",
          submenu: [
            { linkTo: "/link.stream/app/linkstream", label: "Add Link" },
            { linkTo: "#", label: "Manage Links" }
          ]
        },
        {          
          icon: `img:${httpAssets}images/icons/play.svg`,
          label: "Videos",
          linkTo: "#",
          submenu: [
            { linkTo: "#", label: "Add Video" },
            { linkTo: "#", label: "Manage Videos" }
          ]
        },
        {          
          icon: `img:${httpAssets}images/icons/chart-bar.svg`,
          label: "Analytics",
          linkTo: "#",
          submenu: [
            { linkTo: "#", label: "Traffic" },
            { linkTo: "#", label: "Revenue" }
          ]
        },
        {          
          icon: `img:${httpAssets}images/icons/icon-account.svg`,
          label: "Account",
          linkTo: "#",
          submenu: [
            { linkTo: "#", label: "Settings" },
            { linkTo: "#", label: "Billing" }
          ]
        },
        /*{
          icon: "img:assets/images/icons/icon-reports.svg",
          label: "Reports",
          linkTo: "#",
          submenu: []
        },*/
        {          
          icon: `img:${httpAssets}images/icons/icon-upgrade.svg`,
          label: "Upgrade",
          linkTo: "#",
          submenu: []
        }
      ],

      notificationsMenu: [
        {
          icon: "assets/images/testimonials/1.png",
          notification: "theredfantastic started following you 1h"
        },
        {
          icon: "assets/images/testimonials/2.png",
          notification: "jmirra started following you 2d"
        }
      ],
      accountSettings: [
        { setting: "Account Settings" },
        { setting: "Billing History" },
        { setting: "Sign Out" }
      ]
    };
  },
  mounted() {
    this.submenu = false;
  },
  watch: {
    left() {      
      this.left === false
        ? (this.menuState = `${httpAssets}images/icons/bars.svg`)
        : (this.menuState = `${httpAssets}images/icons/icon-close-sharp.svg`);
    },
    submenu() {
      this.submenu ? (this.dynamicWidth = 256) : (this.dynamicWidth = 128);
    },
    focus() {
      console.log(this.focus);
    }
  },
  methods: {
    onItemClick() {
      // console.log('Clicked on an Item')
    },

    away() {
      this.submenu = false;
    },

    setActiveItem(item) {
      this.selectedMenu = item.label;
      if (item.submenu.length !== 0) {
        this.submenuList = item.submenu;
        this.submenu = true;
      } else {
        this.submenu = false;
      }
    }
  }
});
