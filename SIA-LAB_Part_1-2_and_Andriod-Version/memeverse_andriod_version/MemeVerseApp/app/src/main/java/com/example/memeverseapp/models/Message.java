package com.example.memeverseapp.models;

public class Message {
    private int id;
    private int sender_id;
    private int receiver_id;
    private String message;
    private int is_read;
    private String created_at;
    private String username;
    private String nickname;
    private String avatar_url;

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }
    public int getSender_id() { return sender_id; }
    public void setSender_id(int sender_id) { this.sender_id = sender_id; }
    public int getReceiver_id() { return receiver_id; }
    public void setReceiver_id(int receiver_id) { this.receiver_id = receiver_id; }
    public String getMessage() { return message; }
    public void setMessage(String message) { this.message = message; }
    public int getIs_read() { return is_read; }
    public void setIs_read(int is_read) { this.is_read = is_read; }
    public String getCreated_at() { return created_at; }
    public void setCreated_at(String created_at) { this.created_at = created_at; }
    public String getUsername() { return username; }
    public void setUsername(String username) { this.username = username; }
    public String getNickname() { return nickname; }
    public void setNickname(String nickname) { this.nickname = nickname; }
    public String getAvatar_url() { return avatar_url; }
    public void setAvatar_url(String avatar_url) { this.avatar_url = avatar_url; }
    public boolean isRead() { return is_read == 1; }
}