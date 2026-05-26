package com.example.memeverseapp.ui;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.appcompat.app.AlertDialog;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import com.bumptech.glide.Glide;
import com.example.memeverseapp.R;
import com.example.memeverseapp.models.*;
import com.example.memeverseapp.network.ApiService;
import com.example.memeverseapp.network.RetrofitClient;
import com.example.memeverseapp.utils.PreferencesManager;
import com.example.memeverseapp.utils.TimeUtils;
import com.example.memeverseapp.utils.ToastUtils;
import de.hdodenhof.circleimageview.CircleImageView;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
import java.util.ArrayList;
import java.util.List;

public class PostDetailFragment extends Fragment {
    private int postId;
    private Post post;
    private ApiService apiService;
    private PreferencesManager prefManager;
    private ImageView ivImage;
    private CircleImageView ivAvatar;
    private TextView tvUsername, tvTime, tvCategory, tvTitle, tvDescription, tvVoteScore;
    private Button btnUpvote, btnDownvote, btnEdit, btnDelete;
    private RecyclerView rvComments;
    private EditText etComment;
    private Button btnPostComment;
    private CommentAdapter adapter;
    private List<Comment> commentList = new ArrayList<>();

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.activity_post_detail, container, false);

        if (getArguments() != null) {
            postId = getArguments().getInt("post_id", 0);
        }
        if (postId == 0) {
            ToastUtils.showError(requireActivity(), "Invalid post");
            requireActivity().onBackPressed();
            return view;
        }

        initViews(view);
        apiService = RetrofitClient.getClient().create(ApiService.class);
        prefManager = new PreferencesManager(requireContext());
        loadPost();

        return view;
    }

    private void initViews(View view) {
        ivImage = view.findViewById(R.id.ivPostImage);
        ivAvatar = view.findViewById(R.id.ivAvatar);
        tvUsername = view.findViewById(R.id.tvUsername);
        tvTime = view.findViewById(R.id.tvTime);
        tvCategory = view.findViewById(R.id.tvCategory);
        tvTitle = view.findViewById(R.id.tvTitle);
        tvDescription = view.findViewById(R.id.tvDescription);
        tvVoteScore = view.findViewById(R.id.tvVoteScore);
        btnUpvote = view.findViewById(R.id.btnUpvote);
        btnDownvote = view.findViewById(R.id.btnDownvote);
        btnEdit = view.findViewById(R.id.btnEdit);
        btnDelete = view.findViewById(R.id.btnDelete);
        rvComments = view.findViewById(R.id.rvComments);
        etComment = view.findViewById(R.id.etComment);
        btnPostComment = view.findViewById(R.id.btnPostComment);

        rvComments.setLayoutManager(new LinearLayoutManager(getContext()));
        adapter = new CommentAdapter(commentList);
        rvComments.setAdapter(adapter);

        btnUpvote.setOnClickListener(v -> vote("up"));
        btnDownvote.setOnClickListener(v -> vote("down"));
        btnEdit.setOnClickListener(v -> showEditDialog());
        btnDelete.setOnClickListener(v -> confirmDelete());
        btnPostComment.setOnClickListener(v -> addComment());
    }

    private void loadPost() {
        apiService.getPost(postId).enqueue(new Callback<PostDetailResponse>() {
            @Override
            public void onResponse(Call<PostDetailResponse> call, Response<PostDetailResponse> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    post = response.body().getPost();
                    displayPost();
                    commentList.clear();
                    if (post.getComments() != null) commentList.addAll(post.getComments());
                    adapter.notifyDataSetChanged();
                } else {
                    ToastUtils.showError(requireActivity(), "Failed to load post");
                    requireActivity().onBackPressed();
                }
            }
            @Override
            public void onFailure(Call<PostDetailResponse> call, Throwable t) {
                ToastUtils.showError(requireActivity(), "Network error");
                requireActivity().onBackPressed();
            }
        });
    }

    private void displayPost() {
        String imageUrl = RetrofitClient.getFullUrl(post.getImage_path());
        String avatarUrl = RetrofitClient.getFullUrl(post.getAvatar_url());

        Glide.with(requireContext()).load(imageUrl).into(ivImage);
        Glide.with(requireContext()).load(avatarUrl).into(ivAvatar);

        tvUsername.setText(post.getNickname() != null ? post.getNickname() : post.getUsername());
        tvTime.setText(TimeUtils.getTimeAgo(post.getCreated_at()));
        tvCategory.setText(post.getCategory_name());
        tvTitle.setText(post.getTitle());
        tvDescription.setText(post.getDescription());
        tvVoteScore.setText(String.valueOf(post.getVote_score()));

        int vote = post.getUser_vote();
        updateVoteButtons(vote);

        int currentUserId = prefManager.getUserId();
        if (currentUserId == post.getUser_id()) {
            btnEdit.setVisibility(View.VISIBLE);
            btnDelete.setVisibility(View.VISIBLE);
        } else {
            btnEdit.setVisibility(View.GONE);
            btnDelete.setVisibility(View.GONE);
        }
    }

    private void updateVoteButtons(int userVote) {
        btnUpvote.setAlpha(userVote == 1 ? 1.0f : 0.5f);
        btnDownvote.setAlpha(userVote == -1 ? 1.0f : 0.5f);
    }

    private void vote(String voteType) {
        apiService.vote(postId, voteType).enqueue(new Callback<VoteResponse>() {
            @Override
            public void onResponse(Call<VoteResponse> call, Response<VoteResponse> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    post.setVote_score(response.body().getNew_score());
                    tvVoteScore.setText(String.valueOf(post.getVote_score()));
                    int newVote = voteType.equals("up") ? 1 : -1;
                    post.setUser_vote(newVote);
                    updateVoteButtons(newVote);
                    ToastUtils.showSuccess(requireActivity(), "Vote recorded!");
                } else {
                    ToastUtils.showError(requireActivity(), "Vote failed");
                }
            }
            @Override
            public void onFailure(Call<VoteResponse> call, Throwable t) {
                ToastUtils.showError(requireActivity(), "Network error");
            }
        });
    }

    private void showEditDialog() {
        View dialogView = getLayoutInflater().inflate(R.layout.dialog_edit_post, null);
        EditText etTitle = dialogView.findViewById(R.id.etEditTitle);
        EditText etDesc = dialogView.findViewById(R.id.etEditDesc);
        etTitle.setText(post.getTitle());
        etDesc.setText(post.getDescription());

        new AlertDialog.Builder(requireContext())
                .setTitle("Edit Meme")
                .setView(dialogView)
                .setPositiveButton("Save", (d, which) -> {
                    String newTitle = etTitle.getText().toString().trim();
                    String newDesc = etDesc.getText().toString().trim();
                    updatePost(newTitle, newDesc);
                })
                .setNegativeButton("Cancel", null)
                .show();
    }

    private void updatePost(String title, String description) {
        apiService.editPost(postId, title, description, post.getCategory_id()).enqueue(new Callback<ApiResponse>() {
            @Override
            public void onResponse(Call<ApiResponse> call, Response<ApiResponse> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    ToastUtils.showSuccess(requireActivity(), "Post updated");
                    loadPost();
                } else {
                    ToastUtils.showError(requireActivity(), "Update failed");
                }
            }
            @Override
            public void onFailure(Call<ApiResponse> call, Throwable t) {
                ToastUtils.showError(requireActivity(), "Network error");
            }
        });
    }

    private void confirmDelete() {
        new AlertDialog.Builder(requireContext())
                .setTitle("Delete Post")
                .setMessage("Are you sure? This cannot be undone.")
                .setPositiveButton("Delete", (d, which) -> {
                    apiService.deletePost(postId).enqueue(new Callback<ApiResponse>() {
                        @Override
                        public void onResponse(Call<ApiResponse> call, Response<ApiResponse> response) {
                            if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                                ToastUtils.showSuccess(requireActivity(), "Post deleted");
                                requireActivity().onBackPressed();
                            } else {
                                ToastUtils.showError(requireActivity(), "Delete failed");
                            }
                        }
                        @Override
                        public void onFailure(Call<ApiResponse> call, Throwable t) {
                            ToastUtils.showError(requireActivity(), "Network error");
                        }
                    });
                })
                .setNegativeButton("Cancel", null)
                .show();
    }

    private void addComment() {
        String text = etComment.getText().toString().trim();
        if (text.isEmpty()) return;

        apiService.addComment(postId, text, 0).enqueue(new Callback<ApiResponse>() {
            @Override
            public void onResponse(Call<ApiResponse> call, Response<ApiResponse> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    etComment.setText("");
                    loadPost();
                    ToastUtils.showSuccess(requireActivity(), "Comment posted");
                } else {
                    ToastUtils.showError(requireActivity(), "Failed to post comment");
                }
            }
            @Override
            public void onFailure(Call<ApiResponse> call, Throwable t) {
                ToastUtils.showError(requireActivity(), "Network error");
            }
        });
    }

    private class CommentAdapter extends RecyclerView.Adapter<CommentAdapter.ViewHolder> {
        private List<Comment> comments;

        CommentAdapter(List<Comment> comments) { this.comments = comments; }

        @Override
        public ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
            View v = getLayoutInflater().inflate(R.layout.item_comment, parent, false);
            return new ViewHolder(v);
        }

        @Override
        public void onBindViewHolder(ViewHolder holder, int position) {
            Comment c = comments.get(position);
            String displayName = c.getNickname() != null ? c.getNickname() : c.getUsername();
            holder.tvUsername.setText(displayName);
            holder.tvComment.setText(c.getComment_text());
            holder.tvTime.setText(TimeUtils.getTimeAgo(c.getCreated_at()));

            String avatarUrl = RetrofitClient.getFullUrl(c.getAvatar_url());
            if (avatarUrl != null) {
                Glide.with(requireContext()).load(avatarUrl).into(holder.ivAvatar);
            }

            holder.btnReply.setOnClickListener(v -> showReplyDialog(c.getId()));
        }

        @Override public int getItemCount() { return comments.size(); }

        class ViewHolder extends RecyclerView.ViewHolder {
            CircleImageView ivAvatar;
            TextView tvUsername, tvComment, tvTime;
            Button btnReply;

            ViewHolder(View v) {
                super(v);
                ivAvatar = v.findViewById(R.id.ivCommentAvatar);
                tvUsername = v.findViewById(R.id.tvCommentUsername);
                tvComment = v.findViewById(R.id.tvCommentText);
                tvTime = v.findViewById(R.id.tvCommentTime);
                btnReply = v.findViewById(R.id.btnReply);
            }
        }
    }

    private void showReplyDialog(int parentCommentId) {
        EditText input = new EditText(getContext());
        input.setHint("Write a reply...");

        new AlertDialog.Builder(requireContext())
                .setTitle("Reply")
                .setView(input)
                .setPositiveButton("Post", (d, which) -> {
                    String reply = input.getText().toString().trim();
                    if (!reply.isEmpty()) {
                        apiService.addComment(postId, reply, parentCommentId).enqueue(new Callback<ApiResponse>() {
                            @Override
                            public void onResponse(Call<ApiResponse> call, Response<ApiResponse> response) {
                                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                                    loadPost();
                                    ToastUtils.showSuccess(requireActivity(), "Reply posted");
                                } else {
                                    ToastUtils.showError(requireActivity(), "Reply failed");
                                }
                            }
                            @Override
                            public void onFailure(Call<ApiResponse> call, Throwable t) {
                                ToastUtils.showError(requireActivity(), "Network error");
                            }
                        });
                    }
                })
                .setNegativeButton("Cancel", null)
                .show();
    }
}