<!DOCTYPE html>
<html lang="en" dir="">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1" >
        <meta http-equiv="X-UA-Compatible" content="ie=edge" >
        <link rel="icon" href="<?= HTTP_ASSETS ?>images/favicon/streamy_favicon_color.png" type="image/png">
        <link rel="apple-touch-icon" href="<?= HTTP_ASSETS ?>images/favicon/streamy_favicon_color.png">
        <link rel="shortcut icon" href="<?= HTTP_ASSETS ?>images/favicon/streamy_favicon_color_BkH_icon.ico" type="image/x-icon">
        <title><?= $title ?></title>              
        <link href="<?= HTTP_ASSETS ?>node_modules/typeface-montserrat/index.css" rel="stylesheet" > 
        <link href="<?= HTTP_ASSETS ?>node_modules/lato-font/css/lato-font.min.css" rel="stylesheet" > 
        <link href="<?= HTTP_ASSETS ?>images/logo/streamy_icon_RGB.png" rel="shortcut icon" type="image/png">
        <link href="<?= HTTP_ASSETS ?>css/streamy.css" rel="stylesheet">
        <link href="<?= HTTP_ASSETS ?>node_modules/quasar/dist/quasar.min.css" rel="stylesheet" >        
        <link href="<?= HTTP_ASSETS ?>node_modules/roboto-fontface/css/roboto/roboto-fontface.css" rel="stylesheet" >   
        <link href="<?= HTTP_ASSETS ?>node_modules/material-design-icons/iconfont/material-icons.css" rel="stylesheet" >           
    </head>
    <body class="text-left">
        <div id="q-app">
            <template>
                <q-layout view="hHh LpR fFf">
                    <q-header reveal class="bg-black text-white" style="height: 80px">
                        <q-toolbar>
                            <q-toolbar-title class="row items-center content-center">
                                <img src="<?= HTTP_ASSETS ?>images/logo/streamy_icon_RGB.png" alt="streamy" class="q-mt-sm q-pa-none streamy_logo">
                                <img :src="menuState" @click="left = !left" alt="menu" class="q-mt-sm q-ml-sm cursor-pointer menu_icon">      
                            </q-toolbar-title>                                                  
                            <div class="q-gutter-sm row items-center q-mr-sm">
                                <q-btn round dense flat color="grey-8" icon="img:<?= HTTP_ASSETS ?>images/icons/icon-alerts.svg" class="q-mr-none">
                                    <q-badge color="red-12" text-color="white" floating>2</q-badge>
                                    <q-tooltip>Notifications</q-tooltip>
                                    <q-menu :offset="[280, 5]">
                                        <q-list style="width: 303px" bordered separator v-for="(notificationItem, index) in notificationsMenu" :key="index">
                                            <q-item v-close-popup v-ripple>                                            
                                                <q-item-section avatar>                                                
                                                    <q-img :src="notificationItem.icon" sizes="max-width: 40px"/>
                                                </q-item-section>
                                                <q-item-section lines="2">
                                                    <q-item-label caption lines="2">{{ notificationItem.notification }}</q-item-label>                                                
                                                </q-item-section>                                                
                                            </q-item>
                                        </q-list>
                                    </q-menu>
                                </q-btn>
                                <q-btn round flat>
                                    <q-avatar size="40px">
                                        <img src="<?= base_url() . 'app/get_avatar' ?>" alt="avatar" class="self-center">
                                    </q-avatar>
                                    <q-tooltip>Account</q-tooltip>
                                    <q-menu :offset="[100, 5]">
                                        <q-list style="width: 145px" v-for="(settingItem, index) in accountSettings" :key="index">
                                            <q-item v-close-popup v-ripple clickable >  
                                                <q-item-section>
                                                    <q-item-label>{{ settingItem.setting }}</q-item-label>                                                
                                                </q-item-section>
                                            </q-item>
                                        </q-list>
                                    </q-menu>
                                </q-btn>                                    
                            </div>
                        </q-toolbar>
                    </q-header>    
                    <q-drawer show-if-above v-model="left" side="left" :width="dynamicWidth" :content-style="{backgroundColor: '#333333'}" :overlay="submenu" behavior="desktop">                          
                        <q-scroll-area class="fit" :visible="false">                        
                            <div class="row" v-on-clickaway="away">
                                <div class="col"> 
                                    <q-card class="my-card" square flat>                                                           
                                        <q-list v-for="(menuItem, index) in menuList" :key="index" class="text-grey-8">
                                            <q-item clickable style="height: 85px" class="column items-center content-center q-pa-sm" :active="selectedMenu === menuItem.label" 
                                                    @click="setActiveItem(menuItem)" active-class="my-menu-link" @mouseover="setActiveItem(menuItem)" >
                                                    <q-item-section class="q-ml-none q-mt-md">                                       
                                                    <q-icon color="white" size="20px" :name="menuItem.icon" />
                                                </q-item-section>                                               
                                                <q-item-section class="q-mx-none q-mb-sm q-my-none">
                                                    <q-item-label  class="font-menu">{{ menuItem.label }}</q-item-label>
                                                </q-item-section>   
                                                <q-slide-transition>
                                                    <q-item-section >                                       
                                                        <q-separator />
                                                    </q-item-section>                                                                                                                                                                                       
                                            </q-item>
                                            <q-separator dark />
                                        </q-list>
                                    </q-card>
                                </div>
                                <div class="col" style="width:128px" v-show="submenu">
                                    <q-card class="my-card" style="width:128px; height: 100vh; background-color: rgba(0,0,0,0.5); color: white" square flat>                        
                                        <q-list v-for="(menuItem, index) in submenuList" :key="index" class="text-grey-8">
                                            <a :href = menuItem.linkTo style="text-decoration: none;">
                                                <q-item clickable style="height: 15px" class="column items-left content-left q-pa-xs">                                                
                                                    <q-item-section class="q-ml-xs q-mt-xs q-my-none">
                                                        <q-item-label  class="font-submenu">{{ menuItem.label }}</q-item-label>
                                                    </q-item-section> 
                                                    <q-slide-transition>                                                                                              
                                                </q-item> 
                                            </a>
                                        </q-list>
                                    </q-card>    
                                </div>                
                            </div>                            
                        </q-scroll-area>  
                    </q-drawer>                      
                    <div>
                        <q-page-container>   
                            <?= $body_content ?>
                        </q-page-container>
                    </div> 
                </q-layout>
            </template>      
        </div>
        <script> var urlBase = "<?= base_url(); ?>";</script>	
		<script> var httpAssets = "<?= HTTP_ASSETS ?>";</script>
		<script src="<?= HTTP_ASSETS ?>public/app.js"></script>   
    </body>
</html>

<style lang="sass">
    .my-menu-link{ 
        border-left: 6px solid #FDD311;
    }
    .my-card{
        width:128px; 
        min-height: calc(100%); 
        background-color: #333333; 
        color: white;
    }
</style>