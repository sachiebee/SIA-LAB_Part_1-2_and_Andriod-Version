package com.example.memeverseapp.adapters;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;
import com.bumptech.glide.Glide;
import com.example.memeverseapp.R;
import com.example.memeverseapp.models.Post;
import com.example.memeverseapp.network.RetrofitClient;
import com.example.memeverseapp.utils.TimeUtils;
import java.util.List;

public class PostAdapter extends RecyclerView.Adapter<PostAdapter.PostViewHolder> {
    private List<Post> posts;
    private OnVoteClickListener voteListener;
    private OnCommentClickListener commentListener;
    private OnProfileClickListener profileListener;

    public interface OnVoteClickListener { void onVote(int postId, String voteType); }
    public interface OnCommentClickListener { void onCommentClick(int postId); }
    public interface OnProfileClickListener { void onProfileClick(int userId); }

    public PostAdapter(List<Post> posts, OnVoteClickListener voteListener,
                       OnCommentClickListener commentListener, OnProfileClickListener profileListener) {
        this.posts = posts;
        this.voteListener = voteListener;
        this.commentListener = commentListener;
        this.profileListener = profileListener;
    }

    @NonNull
    @Override
    public PostViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_post, parent, false);
        return new PostViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull PostViewHolder holder, int position) {
        Post post = posts.get(position);
        holder.bind(post);
    }

    @Override public int getItemCount() { return posts.size(); }

    class PostViewHolder extends RecyclerView.ViewHolder {
        ImageView ivAvatar, ivPostImage;
        TextView tvUsername, tvTime, tvCategory, tvCaption, tvDescription, tvTags, tvVoteScore, tvCommentCount;
        ImageButton btnUpvote, btnDownvote;
        Button btnComments;

        PostViewHolder(@NonNull View itemView) {
            super(itemView);
            ivAvatar = itemView.findViewById(R.id.ivAvatar);
            ivPostImage = itemView.findViewById(R.id.ivPostImage);
            tvUsername = itemView.findViewById(R.id.tvUsername);
            tvTime = itemView.findViewById(R.id.tvTime);
            tvCategory = itemView.findViewById(R.id.tvCategory);
            tvCaption = itemView.findViewById(R.id.tvCaption);
            tvDescription = itemView.findViewById(R.id.tvDescription);
            tvTags = itemView.findViewById(R.id.tvTags);
            tvVoteScore = itemView.findViewById(R.id.tvVoteScore);
            tvCommentCount = itemView.findViewById(R.id.tvCommentCount);
            btnUpvote = itemView.findViewById(R.id.btnUpvote);
            btnDownvote = itemView.findViewById(R.id.btnDownvote);
            btnComments = itemView.findViewById(R.id.btnComments);
        }

        void bind(Post post) {
            // Handle null username
            String username = post.getNickname();
            if (username == null || username.isEmpty()) {
                username = post.getUsername();
                if (username == null || username.isEmpty()) {
                    username = "Anonymous";
                }
            }
            tvUsername.setText(username);

            // Handle null time
            String time = post.getCreated_at();
            if (time == null || time.isEmpty()) {
                tvTime.setText("recently");
            } else {
                tvTime.setText(TimeUtils.getTimeAgo(time));
            }

            // Handle null category
            String category = post.getCategory_name();
            if (category == null || category.isEmpty()) {
                category = "General";
            }
            tvCategory.setText(category);

            // Handle null title
            String title = post.getTitle();
            if (title == null || title.isEmpty()) {
                title = "Untitled";
            }
            tvCaption.setText(title);

            // Handle null description
            String description = post.getDescription();
            if (description == null || description.isEmpty()) {
                tvDescription.setVisibility(View.GONE);
            } else {
                tvDescription.setVisibility(View.VISIBLE);
                tvDescription.setText(description);
            }

            // Handle null tags
            String tags = post.getTags();
            if (tags == null || tags.isEmpty()) {
                tvTags.setVisibility(View.GONE);
            } else {
                tvTags.setVisibility(View.VISIBLE);
                tvTags.setText(tags);
            }

            tvVoteScore.setText(String.valueOf(post.getVote_score()));
            tvCommentCount.setText(String.valueOf(post.getComment_count()));

            // Handle null avatar URL
            String avatarUrl = RetrofitClient.getFullUrl(post.getAvatar_url());
            if (avatarUrl != null && !avatarUrl.isEmpty()) {
                Glide.with(itemView.getContext())
                        .load(avatarUrl)
                        .placeholder(R.drawable.ic_default_avatar)
                        .error(R.drawable.ic_default_avatar)
                        .into(ivAvatar);
            } else {
                ivAvatar.setImageResource(R.drawable.ic_default_avatar);
            }

            // Handle null image URL
            String imageUrl = RetrofitClient.getFullUrl(post.getImage_path());
            if (imageUrl != null && !imageUrl.isEmpty()) {
                Glide.with(itemView.getContext())
                        .load(imageUrl)
                        .placeholder(R.drawable.ic_placeholder)
                        .error(R.drawable.ic_placeholder)
                        .into(ivPostImage);
            } else {
                ivPostImage.setImageResource(R.drawable.ic_placeholder);
            }

            // Set vote button states
            int vote = post.getUser_vote();
            if (vote == 1) {
                btnUpvote.setColorFilter(ContextCompat.getColor(itemView.getContext(), R.color.primary));
                btnDownvote.setColorFilter(ContextCompat.getColor(itemView.getContext(), R.color.text_muted));
            } else if (vote == -1) {
                btnDownvote.setColorFilter(ContextCompat.getColor(itemView.getContext(), R.color.primary));
                btnUpvote.setColorFilter(ContextCompat.getColor(itemView.getContext(), R.color.text_muted));
            } else {
                btnUpvote.setColorFilter(ContextCompat.getColor(itemView.getContext(), R.color.text_muted));
                btnDownvote.setColorFilter(ContextCompat.getColor(itemView.getContext(), R.color.text_muted));
            }

            btnUpvote.setOnClickListener(v -> voteListener.onVote(post.getId(), "up"));
            btnDownvote.setOnClickListener(v -> voteListener.onVote(post.getId(), "down"));
            btnComments.setOnClickListener(v -> commentListener.onCommentClick(post.getId()));
            ivAvatar.setOnClickListener(v -> profileListener.onProfileClick(post.getUser_id()));
            tvUsername.setOnClickListener(v -> profileListener.onProfileClick(post.getUser_id()));
        }
    }
}