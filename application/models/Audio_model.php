<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of streamy
 *
 * @author paolo
 */
class Audio_model extends CI_Model {

    //put your code here
    public function __construct() {
        parent::__construct();
    }

    public function insert_audio($data) {
        $this->db->insert('st_audio', $data);
        return $this->db->insert_id();
    }

    public function update_streamy($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('st_audio', $data);
    }

    public function fetch_audio_by_id($id) {
        $this->db->from('st_audio');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->row_array();
        $query->free_result();
        return $result;
    }

    public function fetch_audio_by_search($search, $limit = 0, $offset = 0) {
        $this->db->from('st_audio');
        if (!empty($search['user_id'])) {
            $this->db->where('user_id', $search['user_id']); //By Usew
        }
        if (!empty($search['title'])) {
            $this->db->where('title', $search['title']); //By Usew
        }
        if (!empty($search['excluded_id'])) {
            $this->db->where('id <> ', $search['excluded_id']); //By Usew
        }
        if (!empty($search['track_type'])) {
            $this->db->where('track_type', $search['track_type']); //By Usew
        }
        $this->db->where('status_id <> ', '3');
        if (!empty($limit)) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_audio_by_id_user($id, $user_id) {
        $this->db->from('st_audio');
        $this->db->where('id', $id);
        $this->db->where('user_id', $user_id);
        $query = $this->db->get();
        $result = $query->row_array();
        $query->free_result();
        return $result;
    }

//    public function fetch_streamys_by_search($search, $limit = 0, $offset = 0) {
//        $this->db->select('a.*,b.id as type_id,b.type as type_name');
//        $this->db->from('st_streamy a');
//        $this->db->join('st_streamy_type b', 'a.type_id = b.id');
//        if (!empty($search['user'])) {
//            $this->db->where('user_id', $search['user']); //By Usew
//        }
//        if (!empty($search['type'])) {
//            $this->db->where('a.type_id', $search['type']); //By Type 
//        }
//        if (!empty($search['genre'])) {
//            $this->db->where('a.genre_id', $search['genre']); //By Type 
//        }
//        if (!empty($search['name'])) {
//            $this->db->like('a.name', $search['name']); //By Type 
//        }
//        if (!empty($search['public'])) {
//            $this->db->where('public', $search['public']); //By Public
//        }
//        if (!empty($search['status'])) {
//            $this->db->where('status_id', $search['status']); //By Status
//        }
//        if (!empty($search['sort_col'])) {
//            $this->db->order_by($search['sort_col'], $search['sort_dir']);
//        }
//        if (!empty($limit)) {
//            $this->db->limit($limit, $offset);
//        }
//        $query = $this->db->get();
//        $result = $query->result_array();
//        $query->free_result();
//        return $result;
//    }
//
//    public function fetch_streamys_count_by_search($search) {
//        $this->db->select('count(*) as Count');
//        $this->db->from('st_streamy');
//        if (!empty($search['user'])) {
//            $this->db->where('user_id', $search['user']); //By Usew
//        }
//        if (!empty($search['type'])) {
//            $this->db->where('type_id', $search['type']); //By Type 
//        }
//        if (!empty($search['genre'])) {
//            $this->db->where('genre_id', $search['genre']); //By Type 
//        }
//        if (!empty($search['name'])) {
//            $this->db->like('name', $search['name']); //By Type 
//        }
//        if (!empty($search['public'])) {
//            $this->db->where('public', $search['public']); //By Public
//        }
//        if (!empty($search['status'])) {
//            $this->db->where('status_id', $search['status']); //By Status
//        }
//        $query = $this->db->get();
//        $row = $query->row();
//        $query->free_result();
//        return $row->Count;
//    }

    public function fetch_genres() {
        $this->db->from('st_genre');
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_genre_by_id($id) {
        $this->db->from('st_genre');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->row_array();
        $query->free_result();
        return $result;
    }

    public function insert_early_access($data) {
        $this->db->insert('st_early_access', $data);
        return $this->db->insert_id();
    }

//    public function fetch_types() {
//        $this->db->from('st_streamy_type');
//        $this->db->where('active', 1); //By Status
//        $query = $this->db->get();
//        $result = $query->result_array();
//        $query->free_result();
//        return $result;
//    }
//    public function fetch_types_by_id($id) {
//        $this->db->from('st_streamy_type');
//        $this->db->where('id', $id);
//        $query = $this->db->get();
//        $result = $query->row_array();
//        $query->free_result();
//        return $result;
//    }
//    public function fetch_content_order($user_id) {
//        $this->db->from('st_user_streamy_order');
//        $this->db->where('user_id', $user_id); //By Status
//        $query = $this->db->get();
//        $result = $query->result_array();
//        $query->free_result();
//        return $result;
//    }

    public function fetch_related_audio_by_user_id($user_id, $deleted = false) {
        $this->db->select('id, title');
        $this->db->from('st_audio');
        $this->db->where('user_id', $user_id);
        if (!$deleted) {
            $this->db->where('status_id <> ', '3');
        }
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_track_types() {
        $this->db->from('st_track_type');
        $this->db->where('active ', '1');
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_streamys_by_user_id($user_id, $track_type, $audio_id, $deleted = false, $limit = 0, $offset = 0) {
        $this->db->from('st_audio');
        $this->db->where('user_id', $user_id);
        if (!$deleted) {
            $this->db->where('status_id <> ', '3');
        }
        if (!empty($track_type)) {
            $this->db->where('track_type', $track_type);
        }
        if (!empty($audio_id)) {
            $this->db->where('id', $audio_id);
        }
        $this->db->order_by('sort');
        if (!empty($limit)) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_max_audio_sort($user_id) {
        $this->db->select('MAX(sort) as Max');
        $this->db->from('st_audio');
        $this->db->where('user_id', $user_id);
        $query = $this->db->get();
        $row = $query->row();
        $query->free_result();
        return $row->Max;
    }

    public function fetch_related_links($id, $table) {
        $this->db->from('st_related_link');
        $this->db->where('content_id', $id);
        $this->db->where('content_table', $table);
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_timezones($zone) {
        $this->db->select('id, zone');
        $this->db->from('st_timezone');
        if (!empty($zone)) {
            $this->db->where('zone', $zone);
        }
        $this->db->order_by('zone');
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_timezones_by_id($id) {
        $this->db->from('st_timezone');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->row_array();
        $query->free_result();
        return $result;
    }

    public function insert_timezones($data) {
        $this->db->insert('st_timezone', $data);
        return $this->db->insert_id();
    }

    public function fetch_ip_log($ip) {
        $this->db->select('ip');
        $this->db->from('st_ip_log');
        if (!empty($ip)) {
            $this->db->where('ip', $ip);
        }
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function insert_ip_log($data) {
        $this->db->insert('st_ip_log', $data);
        //return $this->db->insert_id();
    }

    public function fetch_audio_key() {
        $this->db->from('st_audio_key');
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function insert_audio_collaborator($data) {
        $this->db->insert('st_audio_collaborator', $data);
        //return $this->db->insert_id();
    }

    public function delete_audio_collaborator($id) {
        $this->db->where('audio_id', $id);
        $this->db->delete('st_audio_collaborator');
    }

    public function fetch_audio_collaborator_by_id($id) {
        $this->db->select('a.user_id, a.profit, a.publishing, b.user_name, b.image');
        $this->db->from('st_audio_collaborator a');
        $this->db->join('st_user b', 'a.user_id = b.id');
        $this->db->where('a.audio_id', $id);
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function insert_audio_license($data) {
        $this->db->insert('st_audio_license', $data);
        //return $this->db->insert_id();
    }

    public function delete_audio_license($id) {
        $this->db->where('audio_id', $id);
        $this->db->delete('st_audio_license');
    }

    public function fetch_audio_license_by_id($id) {
        $this->db->select('a.license_id, a.price, a.status_id, b.mp3, b.wav, b.trackout_stems');
        $this->db->from('st_audio_license a');
        $this->db->join('st_user_license b', 'a.license_id = b.id');
        $this->db->where('a.audio_id', $id);
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function insert_audio_marketing($data) {
        $this->db->insert('st_audio_marketing', $data);
        //return $this->db->insert_id();
    }

    public function delete_audio_marketing($id) {
        $this->db->where('audio_id', $id);
        $this->db->delete('st_audio_marketing');
    }

    public function fetch_audio_marketing_by_id($id) {
        $this->db->select('marketing_id, connect_id');
        $this->db->from('st_audio_marketing');
        $this->db->where('audio_id', $id);
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    //PUBLIC PROFILE - track_type (1=Song, 2=Beat, 3=Sound Kit)
    public function fetch_sound_kit_by_profile($user_id, $audio_id, $genre, $tag, $sort = 'default', $limit = 0, $offset = 0) {
        $this->db->from('st_audio');
        $this->db->where('user_id', $user_id);
        $this->db->where('status_id <> ', '3');
        $this->db->where('public', '1');
        $this->db->where('track_type', '3');
        if (!empty($audio_id)) {
            $this->db->where('id', $audio_id);
        }
        if (!empty($genre)) {
            //$this->db->where('genre_id', $genre);
            $genres = explode(',', $genre);
            $this->db->where_in('genre_id', $genres);
        }
        if (!empty($tag)) {
            $this->db->where("(`title` LIKE '$tag%' OR `tags` LIKE '$tag%')", null, false);
        }
        if ($sort == 'default') {
            $this->db->order_by('sort');
        } elseif ($sort == 'new') {
            $this->db->order_by('id', 'DESC');
        } elseif ($sort == 'price_low') {
            $this->db->order_by('price');
        } elseif ($sort == 'price_high') {
            $this->db->order_by('price', 'DESC');
        } elseif ($sort == 'best') {
            $this->db->order_by('sort'); //TEMP
        } else {
            $this->db->order_by('sort');
        }
        if (!empty($limit)) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_beat_by_profile($user_id, $audio_id, $genre, $tag, $bpm_min, $bpm_max, $sort = 'default', $limit = 0, $offset = 0) {
        $this->db->from('st_audio');
        $this->db->where('user_id', $user_id);
        $this->db->where('status_id <> ', '3');
        $this->db->where('public', '1');
        $this->db->where('track_type', '2');
        if (!empty($audio_id)) {
            $this->db->where('id', $audio_id);
        }
        if (!empty($genre)) {
            //$this->db->where('genre_id', $genre);
            $genres = explode(',', $genre);
            $this->db->where_in('genre_id', $genres);
        }
        if (!empty($tag)) {
            $this->db->where("(`title` LIKE '$tag%' OR `tags` LIKE '$tag%')", null, false);
        }
        if (!empty($bpm_min)) {
            $this->db->where('bpm >= ', $bpm_min);
        }
        if (!empty($bpm_max)) {
            $this->db->where('bpm <= ', $bpm_max);
        }
        if ($sort == 'default') {
            $this->db->order_by('sort');
        } elseif ($sort == 'new') {
            $this->db->order_by('id', 'DESC');
        } elseif ($sort == 'price_low') {
            $this->db->order_by('price');
        } elseif ($sort == 'price_high') {
            $this->db->order_by('price', 'DESC');
        } elseif ($sort == 'best') {
            $this->db->order_by('sort'); //TEMP
        } else {
            $this->db->order_by('sort');
        }
        if (!empty($limit)) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_beats_by_profile($user_id, $audio_id, $genre, $tag, $bpm_min, $bpm_max, $beat_type, $sort = 'default', $limit = 0, $offset = 0) {
        //
        $query_beat = "SELECT ";
        $query_beat .= "id, created_at, user_id, status_id, title, bpm, key_id, coverart, public, publish_at, sort, genre_id, track_type, tags, untagged_mp3, untagged_wav_name, untagged_wav, track_stems_name, track_stems, tagged_file_name, tagged_file, price, samples, description, '' as license_id, 'beat' as type ";
        $query_beat .= "FROM st_audio ";
        $query_beat .= "WHERE user_id = '" . $user_id . "' AND status_id <> '3' AND public = '1' AND track_type = '2'  ";
        if (!empty($audio_id)) {
            $query_beat .= "AND id = '" . $audio_id . "' ";
        }
        if (!empty($genre)) {
            //print_r($genre);
            $genres = explode(',', $genre);
            //print_r($genres);
            $genres = implode("','", $genres);
            //print_r($genres);
            //$genres = "'".$genres."'";
            //print_r($genres);
            $query_beat .= "AND genre_id in ('" . $genres . "') ";
        }
        if (!empty($tag)) {
            $query_beat .= "AND (title LIKE '%" . $tag . "%' OR tags LIKE '%" . $tag . "%') ";
        }
        if (!empty($bpm_min)) {
            $query_beat .= "AND bpm >= '" . $bpm_min . "' ";
        }
        if (!empty($bpm_max)) {
            $query_beat .= "AND bpm <= '" . $bpm_max . "' ";
        }
        //
        //
        //
        $query_pack = "SELECT ";
        $query_pack .= "id, created_at, user_id, status_id, title, '' as bpm, '' as key_id, coverart, public, publish_at, sort, genre_id, track_type, tags, '' as untagged_mp3, '' as untagged_wav_name, '' as untagged_wav, '' as track_stems_name, '' as track_stems, '' as tagged_file_name, '' as tagged_file, price, '' as samples, description, license_id, 'pack' as type ";
        $query_pack .= "FROM st_album ";
        $query_pack .= "WHERE user_id = '" . $user_id . "' AND status_id <> '3' AND public = '1' AND track_type = '2'  ";
        if (!empty($audio_id)) {
            $query_pack .= "AND id = '" . $audio_id . "'";
        }
        if (!empty($genre)) {
             //print_r($genre);
            $genres = explode(',', $genre);
            //print_r($genres);
            $genres = implode("','", $genres);
            //print_r($genres);
            //$genres = "'".$genres."'";
            //print_r($genres);
            $query_pack .= "AND genre_id in ('" . $genres . "') ";
        }
        if (!empty($tag)) {
            $query_pack .= "AND (title LIKE '%" . $tag . "%' OR tags LIKE '%" . $tag . "%') ";
        }
        //END QUERY
        if ($beat_type == 'beat') {
            $sql = $query_beat;
        } elseif ($beat_type == 'pack') {
            $sql = $query_pack;
        } else {
            $sql = "SELECT * FROM ( " . $query_beat . " UNION ALL " . $query_pack . ") Beats ";
        }
        if ($sort == 'default') {
            $sql .= " order by sort";
        } elseif ($sort == 'new') {
            $sql .= " order by id DESC ";
        } elseif ($sort == 'price_low') {
            $sql .= " order by price";
        } elseif ($sort == 'price_high') {
            $sql .= " order by price DESC";
        } elseif ($sort == 'best') {
            $sql .= " order by sort"; //TEMP UNTIL DEFINE BEST
        }elseif ($sort == 'random') {
            $sql .= " order by RAND()"; 
        } else {
            $this->db->order_by('sort');
            $sql .= " order by sort";
        }
        if (!empty($limit)) {
            $sql .= " limit " . $offset . " , " . $limit;
        }


        $query = $this->db->query($sql);
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_beats_genres_by_profile($user_id) {
        $sql = "SELECT * FROM (
SELECT b.id, b.genre FROM st_audio a inner join st_genre b on a.genre_id = b.id where a.user_id = '" . $user_id . "' AND a.status_id <> '3' AND a.public = '1' AND a.track_type = '2'
UNION
SELECT b.id, b.genre FROM st_album a inner join st_genre b on a.genre_id = b.id where a.user_id = '" . $user_id . "' AND a.status_id <> '3' AND a.public = '1' AND a.track_type = '2'
) Genres order by id ";
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_sound_kits_genres_by_profile($user_id) {
        $sql = "SELECT b.id, b.genre FROM st_audio a inner join st_genre b on a.genre_id = b.id where a.user_id = '" . $user_id . "' AND a.status_id <> '3' AND a.public = '1' AND a.track_type = '3' group by b.id ";
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

}
