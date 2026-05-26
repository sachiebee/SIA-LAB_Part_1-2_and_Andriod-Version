package com.example.memeverseapp.models;

public class Comment {
    private int id;
    private int user_id;
    private int post_id;
    private Integer parent_id;
    private String comment_text;
    private String created_at;
    private String username;
    private String nickname;
    private String avatar_url;

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }
    public int getUser_id() { return user_id; }
    public void setUser_id(int user_id) { this.user_id = user_id; }
    public int getPost_id() { return post_id; }
    public void setPost_id(int post_id) { this.post_id = post_id; }
    public Integer getParent_id() { return parent_id; }
    public void setParent_id(Integer parent_id) { this.parent_id = parent_id; }
    public String getComment_text() { return comment_text; }
    public void setComment_text(String comment_text) { this.comment_text = comment_text; }
    public String getCreated_at() { return created_at; }
    public void setCreated_at(String created_at) { this.created_at = created_at; }
    public String getUsername() { return username; }
    public void setUsername(String username) { this.username = username; }
    public String getNickname() { return nickname; }
    public void setNickname(String nickname) { this.nickname = nickname; }
    public String getAvatar_url() { return avatar_url; }
    public void setAvatar_url(String avatar_url) { this.avatar_url = avatar_url; }
}