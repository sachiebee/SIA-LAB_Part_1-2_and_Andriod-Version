package com.example.memeverseapp.models;

public class Conversation {
    private int id;
    private String username;
    private String nickname;
    private String avatar_url;
    private String last_msg;
    private int unread;

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }
    public String getUsername() { return username; }
    public void setUsername(String username) { this.username = username; }
    public String getNickname() { return nickname; }
    public void setNickname(String nickname) { this.nickname = nickname; }
    public String getAvatar_url() { return avatar_url; }
    public void setAvatar_url(String avatar_url) { this.avatar_url = avatar_url; }
    public String getLast_msg() { return last_msg; }
    public void setLast_msg(String last_msg) { this.last_msg = last_msg; }
    public int getUnread() { return unread; }
    public void setUnread(int unread) { this.unread = unread; }
}