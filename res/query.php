<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
echo '';

function check_user_name_only($username) {
    $username = mysql_real_escape_string($username);

    $checkusername = mysql_query("SELECT * FROM users WHERE username = '" . $username . "'");

    return $checkusername;
}

function check_user_name($username, $password) {
    $username = mysql_real_escape_string($username);
    $password = md5(mysql_real_escape_string($password));

    $checklogin = mysql_query("SELECT * FROM users WHERE username = '" . $username . "' AND password = '" . $password . "'");

    return $checklogin;
}

function register_user($username, $password, $email) {
    $username = mysql_real_escape_string($username);
    $password = md5(mysql_real_escape_string($password));
    $email = mysql_real_escape_string($email);

    $registerquery = mysql_query("INSERT INTO users (username, password, emailAddress) VALUES('" . $username . "', '" . $password . "', '" . $email . "')");

    return $registerquery;
}

function get_user_id($username) {
    $username = mysql_real_escape_string($username);

    $userid_query = mysql_query("SELECT id FROM users WHERE username = '" . $username . "'");
    $result = mysql_fetch_assoc($userid_query);
    return $result['id'];
}

function get_level($path) {
    $folder_level = mysql_query("SELECT level FROM file_folder_permission WHERE path = '" . $path . "'");
    $result = mysql_fetch_assoc($folder_level);
    return $result['level'];
}

function set_level($path, $level) {
    $result = mysql_query("UPDATE file_folder_permission SET level='" . $level . "' WHERE path='" . $path . "'");
    return $result;
}

function register_user_role($userid, $roleid) {
    $userid = mysql_real_escape_string($userid);
    $roleid = mysql_real_escape_string($roleid);
    $register_user_role = mysql_query("INSERT INTO users2role (userid, roleid) VALUES('" . $userid . "', '" . $roleid . "')");
    return $register_user_role;
}

function register_file_permission($ownerid, $name, $path, $level, $parent, $type, $read, $edit, $write, $delete, $move, $share) {
    $register_file_permission = mysql_query("INSERT INTO file_folder_permission
                            (ownerid, name, path, level, parent, type, read_permission_level, edit_permission_level,
                            write_permission_level, delete_permission_level, move_permission_level, share_permission_level) VALUES
                            ('" . $ownerid . "','" . $name . "', '" . $path . "', '" . $level . "', '" . $parent . "', '" . $type . "', '" . $read . "','" . $edit . "','"
                    . $write . "','" . $delete . "','" . $move . "','" . $share . "' )");
    return $register_file_permission;
}

function get_type_number($username, $type) {
    // count the total number of accessable files
    $username = mysql_real_escape_string($username);
    $result = mysql_query("SELECT COUNT(type) FROM file_folder_permission, users, users2role, role
                            WHERE users.username = '" . $username . "' AND type = '" . $type . "' AND users.id=file_folder_permission.ownerid
                            AND ownerid=userid AND roleid=role.id AND role.read_permission_level>=file_folder_permission.read_permission_level");
    $row = mysql_fetch_assoc($result);
    return $row['COUNT(type)'];
}

function get_permission($path, $username, $permission_type) {
    $username = mysql_real_escape_string($username);
    $ptype = null;
    switch ($permission_type) {
        case "read":
            $ptype = "read_permission_level";
            break;
        case "edit":
            $ptype = "edit_permission_level";
            break;
        case "write":
            $ptype = "write_permission_level";
            break;
        case "delete":
            $ptype = "delete_permission_level";
            break;
        case "move":
            $ptype = "move_permission_level";
            break;
        case "share":
            $ptype = "share_permission_level";
            break;
        default:
            return "error";
            break;
    }

    if ($ptype != null) {
        $result = mysql_query("SELECT * FROM users, role, users2role, file_folder_permission
                WHERE username='" . $username . "' AND path='" . $path . "' AND
                users.id=users2role.userid  AND
                users2role.roleid=role.id AND
                role." . $ptype . ">=file_folder_permission." . $ptype);
        // "AND users.id = ownerid" do not need this
        $row = mysql_num_rows($result);
        if ($row >= 1) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function get_name($path) {
    $name = mysql_query("SELECT name FROM file_folder_permission WHERE path = '" . $path . "'");
    $result = mysql_fetch_assoc($name);
    return $result['name'];
}

function get_parent_folder($path) {
    $folder_level = mysql_query("SELECT parent FROM file_folder_permission WHERE path = '" . $path . "'");
    $result = mysql_fetch_assoc($folder_level);
    return $result['parent'];
}

function SetNewName($oldpath, $newpath, $newname) {
    $success = mysql_query("UPDATE file_folder_permission SET name ='" . $newname . "' , path = '" . $newpath . "'
        WHERE path='" . $oldpath . "'");

    return $success;
}

function set_new_path_and_parent($oldpath, $newpath, $newParent) {
    $success = mysql_query("UPDATE file_folder_permission SET path ='" . $newpath . "' , parent = '" . $newParent . "'
        WHERE path='" . $oldpath . "'");
    return $success;
}

function get_affected_child_folders($path) {
    $result = array();
    $a = 0;
    // example: file/a/b/c/% to match all children
    //  exclude itself (file/a/b/c/)
    $pathpattern = $path . "%";
    $query = mysql_query("SELECT path, parent, level, read_permission_level, share_permission_level FROM file_folder_permission WHERE parent LIKE '" . $pathpattern . "'
     AND path <> '" . $path . "'");
    while ($row = mysql_fetch_assoc($query)) {
        $result[$a]['path'] = $row['path'];
        $result[$a]['parent'] = $row['parent'];
        $result[$a]['level'] = $row['level'];
        $result[$a]['read_permission_level'] = $row['read_permission_level'];
        $result[$a]['share_permission_level'] = $row['share_permission_level'];
        $a++;
    }
    return $result;
}

function delete_file($path) {
    $id = get_id($path);
    $result = mysql_query("DELETE FROM file_folder_permission WHERE path='" . $path . "'");
    $result2 = mysql_query("DELETE FROM tags WHERE documentID = '" . $id . "'");

    return $result && $result2;
}

function get_type($path) {
    $query = mysql_query("SELECT type FROM file_folder_permission WHERE path='" . $path . "'");
    $row = mysql_fetch_assoc($query);
    return $row['type'];
}

function get_id($path) {
    $query = mysql_query("SELECT id FROM file_folder_permission WHERE path='" . $path . "'");
    $row = mysql_fetch_assoc($query);
    return $row['id'];
}

function check_tag($path, $tag) {
    $id = get_id($path);
    if ($id > 0) {
        $result = mysql_query("SELECT * FROM tags WHERE documentID = '" . $id . "' AND tag='" . $tag . "'");
        $number = mysql_num_rows($result);
        if ($number > 0) {
            return true;
        } else {
            return false;
        }
    } else {
        return 'error';
    }
}

function add_tag($path, $tag) {
    $id = get_id($path);
    $result = mysql_query("INSERT INTO tags (documentID, tag) VALUES ('" . $id . "', '" . $tag . "')");
    return $result;
}

function delete_tag($id) {
    $result = mysql_query("DELETE FROM tags WHERE id='" . $id . "'");
    return $result;
}

function get_all_tags($path) {
    $result = array();
    $id = get_id($path);
    $query = mysql_query("SELECT tag FROM tags WHERE documentID = '" . $id . "'");
    $a = 0;
    while ($row = mysql_fetch_assoc($query)) {
        $result[$a]['tag'] = $row['tag'];
        $a++;
    }
    return $result;
}

function check_public_access($path) {
    $query = mysql_query("SELECT open_to_public FROM file_folder_permission WHERE path='" . $path . "'");
    $number = mysql_num_rows($query);
    if ($number > 0) {
        return true;
    } else {
        return false;
    }
}

function get_path($id) {
    $query = mysql_query("SELECT path FROM file_folder_permission WHERE id ='" . $id . "'");
    $row = mysql_fetch_assoc($query);
    return $row['path'];
}

function get_owner_name($path) {
    $query = mysql_query("SELECT username FROM users, file_folder_permission WHERE path = '" . $path . "' AND ownerid = users.id");
    $row = mysql_fetch_assoc($query);
    return $row['username'];
}

function get_share_permission($path) {
    $query = mysql_query("SELECT read_permission_level, edit_permission_level, write_permission_level,
                        delete_permission_level, move_permission_level, share_permission_level FROM file_folder_permission WHERE path ='" . $path . "'");
    $row = mysql_fetch_assoc($query);
    return $row;
}

function set_share_permission($path, $newValue, $type) {
    $pType = null;
    switch ($type) {
        case "read":
            $pType = "read_permission_level";
            break;
        case "write";
            $pType = "write_permission_level";
            break;
        default:
            return "error";
            break;
    }
    if ($pType != null) {
        $query = mysql_query("UPDATE file_folder_permission SET " . $pType . " = '" . $newValue . "'
                                WHERE path = '" . $path . "'");
        return $query;
    } else {
        return false;
    }
}

function get_open_to_public_status($path) {
    $query = mysql_query("SELECT open_to_public FROM file_folder_permission WHERE path='" . $path . "' AND type='file'");
    $row = mysql_fetch_assoc($query);
    return $row['open_to_public'];
}

function set_open_to_public_status($path, $status) {
    if ($status == "true" || $status == "false") {
        $query = mysql_query("UPDATE file_folder_permission SET open_to_public = '" . $status . "' WHERE path='" . $path . "' AND type='file'");
        return $query;
    } else {
        return false;
    }
}

function set_filehash($path, $filehash) {
    $query = mysql_query("UPDATE file_folder_permission SET filehash = '" . $filehash . "' WHERE path='" . $path . "' AND type='file'");
    return $query;
}

function get_filehash($path) {
    $query = mysql_query("SELECT filehash FROM file_folder_permission WHERE path = '" . $path . "' AND type='file'");
    $row = mysql_fetch_assoc($query);
    if ($row['filehash'] != null) {
        return $row['filehash'];
    } else {
        return false;
    }
}

function get_user_own_share_document($username, $type) {
    $query = mysql_query("SELECT * FROM users, role, users2role, file_folder_permission
                WHERE username='" . $username . "' AND users.id=users2role.userid AND
                users.id = ownerid AND users2role.roleid=role.id AND
                role.read_permission_level >= file_folder_permission.read_permission_level AND
                file_folder_permission.share_permission_level > file_folder_permission.read_permission_level AND
                file_folder_permission.type='" . $type . "'");
    $a = 0;
    $result = array();
    while ($row = mysql_fetch_assoc($query)) {
        $result[$a]['name'] = $row['name'];
        $result[$a]['path'] = $row['path'];
        $result[$a]['parent'] = $row['parent'];
        $result[$a]['level'] = $row['level'];
        $a++;
    }
    return $result;
}

function get_shared_folder($username, $whether_is_own) {
    if ($whether_is_own) {
        // own shared folder
        $query = mysql_query("SELECT * FROM users, file_folder_permission WHERE users.id = ownerid AND
                              username = '" . $username . "' AND share_permission_level > read_permission_level AND
                                  type = 'folder'");
        $a = 0;
        $result = array();
        while ($row = mysql_fetch_assoc($query)) {
            $result[$a]['username'] = $row['username'];
            $result[$a]['name'] = $row['name'];
            $result[$a]['path'] = $row['path'];
            $result[$a]['parent'] = $row['parent'];
            $result[$a]['level'] = $row['level'];
            $result[$a]['read_permission_level'] = $row['read_permission_level'];
            $a++;
        }
        return $result;
    } elseif (!$whether_is_own) {
        // shared folder of other users
        $query = mysql_query("SELECT * FROM users, file_folder_permission WHERE users.id = ownerid AND
                              username <> '" . $username . "' AND share_permission_level > read_permission_level AND
                                  type = 'folder'");
        $a = 0;
        $result = array();
        while ($row = mysql_fetch_assoc($query)) {
            $result[$a]['username'] = $row['username'];
            $result[$a]['name'] = $row['name'];
            $result[$a]['path'] = $row['path'];
            $result[$a]['parent'] = $row['parent'];
            $result[$a]['level'] = $row['level'];
            $result[$a]['read_permission_level'] = $row['read_permission_level'];
            $a++;
        }
        return $result;
    } else {
        echo "error.";
    }
}

function is_owner($username, $path) {
    $query = mysql_query("SELECT * FROM users, file_folder_permission WHERE
                            path = '" . $path . "' AND username='" . $username . "' AND
                                ownerid = users.id");
    $row = mysql_num_rows($query);
    if ($row > 0) {
        return true;
    } else {
        return false;
    }
}

function search_own($username, $keyword) {
    $keyword = "%" . $keyword . "%";
    $id = get_user_id($username);
    if ($id != null) {
        $query = mysql_query("SELECT file_folder_permission.id, name, path, type, read_permission_level, tag
            FROM file_folder_permission LEFT OUTER JOIN tags ON
            file_folder_permission.id = tags.documentId
            WHERE ownerid = '" . $id . "' AND ( tag LIKE '" . $keyword . "' OR name LIKE '" . $keyword . "' ) ");

        $a = 0;
        $result = array();
        while ($row = mysql_fetch_assoc($query)) {
            $result[$a]['name'] = $row['name'];
            $result[$a]['path'] = $row['path'];
            $result[$a]['type'] = $row['type'];
            $result[$a]['read_permission_level'] = $row['read_permission_level'];
            $result[$a]['tag'] = $row['tag'];
            $a++;
        }
        return $result;
    } else {
        return false;
    }
}

function search_share($username, $keyword) {
    $keyword = "%" . $keyword . "%";
    $id = get_user_id($username);
    if ($id != null) {
        $query = mysql_query("SELECT file_folder_permission.id, name, path, type, read_permission_level, tag
            FROM file_folder_permission LEFT OUTER JOIN tags ON
            file_folder_permission.id = tags.documentId
            WHERE ownerid <> '" . $id . "' AND ( tag LIKE '" . $keyword . "' OR name LIKE '" . $keyword . "' ) ");

        $a = 0;
        $result = array();
        $permissionArray = array();
        while ($row = mysql_fetch_assoc($query)) {
            $permissionArray = get_share_permission($row['path']);
            if ($permissionArray['share_permission_level'] > $permissionArray['read_permission_level']) {
                $result[$a]['name'] = $row['name'];
                $result[$a]['path'] = $row['path'];
                $result[$a]['type'] = $row['type'];
                $result[$a]['read_permission_level'] = $row['read_permission_level'];
                $result[$a]['tag'] = $row['tag'];
                $a++;
            }
        }
        return $result;
    } else {
        return false;
    }
}

function is_admin($username) {
    $query = mysql_query("SELECT * FROM users, users2role, role WHERE users.id = users2role.userid
                          AND users.username = '" . $username . "' AND users2role.roleid = role.id AND
                          (role.id = 1 OR role.id = 2)");
    $count = mysql_num_rows($query);
    if ($count > 0) {
        return true;
    } else {
        return false;
    }
}

function get_user_permission($username) {
    $query = mysql_query("SELECT * FROM users, users2role, role WHERE username='" . $username . "'
                            AND users.id = users2role.userid AND role.id = users2role.roleid");
    if (mysql_num_rows($query) > 0) {
        $result = array();
        $row = mysql_fetch_assoc($query);
        $result['username'] = $row['username'];
        $result['read_permission_level'] = $row['read_permission_level'];
        $result['edit_permission_level'] = $row['edit_permission_level'];
        $result['write_permission_level'] = $row['write_permission_level'];
        $result['delete_permission_level'] = $row['delete_permission_level'];
        $result['move_permission_level'] = $row['move_permission_level'];
        $result['share_permission_level'] = $row['share_permission_level'];
        return $result;
    } else {
        return false;
    }
}

function get_role_list($username) {
    $userPermission = array();
    $userPermission = get_user_permission($username);
    if (count($userPermission) > 0) {
        $query = mysql_query("SELECT * FROM role WHERE read_permission_level<'" . $userPermission['read_permission_level'] . "'
                            AND edit_permission_level<'" . $userPermission['edit_permission_level'] . "' AND
                            write_permission_level<'" . $userPermission['write_permission_level'] . "' AND
                            delete_permission_level<'" . $userPermission['delete_permission_level'] . "' AND
                            move_permission_level<'" . $userPermission['move_permission_level'] . "' AND
                            share_permission_level<'" . $userPermission['share_permission_level'] . "'");
//        $result = mysql_fetch_assoc($query);
        $result = array();
        while ($row = mysql_fetch_assoc($query)) {
            $result[] = $row;
        }
        return $result;
    } else {
        return false;
    }
}

function set_role($id, $name, $description, $read, $edit, $write, $delete, $move, $share) {
    $query = mysql_query("UPDATE role SET name='" . $name . "', description='" . $description . "',
                read_permission_level='" . $read . "', edit_permission_level='" . $edit . "',
                write_permission_level='" . $write . "', delete_permission_level='" . $delete . "',
                move_permission_level='" . $move . "', share_permission_level='" . $share . "'
                WHERE id='" . $id . "'");
    return $query;
}

function add_role($name, $description, $read, $edit, $write, $delete, $move, $share) {
    $query = mysql_query("INSERT INTO role (name, description, read_permission_level, edit_permission_level,
                        write_permission_level, delete_permission_level, move_permission_level, share_permission_level)
                        VALUES ('" . $name . "', '" . $description . "', '" . $read . "', '" . $edit . "', '" . $write . "', '" . $delete . "',
                           '" . $move . "', '" . $share . "')");
    return $query;
}

function get_role_mapping_list($username) {
    $userPermission = array();
    $userPermission = get_user_permission($username);
    if (count($userPermission) > 0) {
        $query = mysql_query("SELECT name,read_permission_level,edit_permission_level,write_permission_level,
                            delete_permission_level,move_permission_level,share_permission_level,
                            username,userid,roleid FROM role,users,users2role WHERE read_permission_level<'" . $userPermission['read_permission_level'] . "'
                            AND edit_permission_level<'" . $userPermission['edit_permission_level'] . "' AND
                            write_permission_level<'" . $userPermission['write_permission_level'] . "' AND
                            delete_permission_level<'" . $userPermission['delete_permission_level'] . "' AND
                            move_permission_level<'" . $userPermission['move_permission_level'] . "' AND
                            share_permission_level<'" . $userPermission['share_permission_level'] . "' AND
                            users.id=users2role.userid AND users2role.roleid=role.id");
        $result = array();
        while ($row = mysql_fetch_assoc($query)) {
            $result[] = $row;
        }
        return $result;
    } else {
        return false;
    }
}

function get_role_names($username) {
    $userPermission = array();
    $userPermission = get_user_permission($username);
    if (count($userPermission) > 0) {
        $query = mysql_query("SELECT role.id AS id,name FROM role WHERE read_permission_level<'" . $userPermission['read_permission_level'] . "'
                            AND edit_permission_level<'" . $userPermission['edit_permission_level'] . "' AND
                            write_permission_level<'" . $userPermission['write_permission_level'] . "' AND
                            delete_permission_level<'" . $userPermission['delete_permission_level'] . "' AND
                            move_permission_level<'" . $userPermission['move_permission_level'] . "' AND
                            share_permission_level<'" . $userPermission['share_permission_level'] . "'");
        $result = array();
        while ($row = mysql_fetch_assoc($query)) {
            $result[] = $row;
        }
        return $result;
    } else {
        return false;
    }
}

function set_user_role($userid, $new_roleid) {
    $query = mysql_query("UPDATE users2role SET roleid='" . $new_roleid . "' WHERE
                         userid='" . $userid . "'");
    return $query;
}

function get_all_users($username) {
    $userPermission = array();
    $userPermission = get_user_permission($username);
    if (count($userPermission) > 0) {
        $query = mysql_query("SELECT users.id AS id, username FROM users, users2role, role WHERE
                          users.id=users2role.userid AND role.id=users2role.roleid AND
                         read_permission_level<'" . $userPermission['read_permission_level'] . "'
                         AND edit_permission_level<'" . $userPermission['edit_permission_level'] . "' AND
                         write_permission_level<'" . $userPermission['write_permission_level'] . "' AND
                         delete_permission_level<'" . $userPermission['delete_permission_level'] . "' AND
                         move_permission_level<'" . $userPermission['move_permission_level'] . "' AND
                         share_permission_level<'" . $userPermission['share_permission_level'] . "'");
        if (mysql_num_rows($query) > 0) {
            $result = array();
            while ($row = mysql_fetch_assoc($query)) {
                $result[] = $row;
            }
            return $result;
        }
        return false;
    } else {
        return false;
    }
}

function get_all_document_permission_by_user($admin, $userid) {
    $userPermission = array();
    $userPermission = get_user_permission($admin);
    if (count($userPermission) > 0) {
        $query = mysql_query("SELECT file_folder_permission.name AS name, path, type, file_folder_permission.read_permission_level,
            file_folder_permission.edit_permission_level, file_folder_permission.write_permission_level,
            file_folder_permission.delete_permission_level, file_folder_permission.move_permission_level,
            file_folder_permission.share_permission_level, open_to_public
            FROM file_folder_permission, users2role, role WHERE 
            users2role.roleid=role.id AND users2role.userid='" . $userid . "' AND
            ownerid='" . $userid . "' AND role.read_permission_level<'" . $userPermission['read_permission_level'] . "'
            AND role.edit_permission_level<'" . $userPermission['edit_permission_level'] . "' AND
            role.write_permission_level<'" . $userPermission['write_permission_level'] . "' AND
            role.delete_permission_level<'" . $userPermission['delete_permission_level'] . "' AND
            role.move_permission_level<'" . $userPermission['move_permission_level'] . "' AND
            role.share_permission_level<'" . $userPermission['share_permission_level'] . "' ");
        if (mysql_num_rows($query) > 0) {
            $result = array();
            while ($row = mysql_fetch_assoc($query)) {
                $result[] = $row;
            }
            return $result;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function set_document_permission($path, $read, $edit, $write, $delete, $move, $share, $publicity, $filehash) {
    $query = mysql_query("UPDATE file_folder_permission SET read_permission_level ='" . $read . "',
                         edit_permission_level='" . $edit . "', write_permission_level='" . $write . "',
                         delete_permission_level='" . $delete . "', move_permission_level = '" . $move . "',
                         share_permission_level='" . $share . "', open_to_public='" . $publicity . "',
                         filehash='" . $filehash . "' WHERE path='" . $path . "'");
    return $query;
}

function get_files_in_folder($path) {
    $query = mysql_query("SELECT path,read_permission_level,share_permission_level FROM file_folder_permission WHERE
                        type='file' AND parent='" . $path . "'");
    if (mysql_num_rows($query) > 0) {
        $result = array();
        while ($row = mysql_fetch_assoc($query)) {
            $result[] = $row;
        }
        return $result;
    } else {
        return false;
    }
}

?>
