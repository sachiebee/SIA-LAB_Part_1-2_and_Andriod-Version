package com.example.memeverseapp.ui.fragments;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.View;
import android.view.ViewGroup;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.fragment.app.Fragment;
import com.example.memeverseapp.R;

public class SearchFragment extends Fragment {

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        // Create a simple programmatic UI instead of relying on XML
        LinearLayout layout = new LinearLayout(getContext());
        layout.setOrientation(LinearLayout.VERTICAL);
        layout.setPadding(32, 32, 32, 32);

        TextView tvTitle = new TextView(getContext());
        tvTitle.setText("🔍 SEARCH");
        tvTitle.setTextSize(24f);
        tvTitle.setTextAlignment(View.TEXT_ALIGNMENT_CENTER);
        tvTitle.setPadding(0, 0, 0, 32);

        EditText etSearch = new EditText(getContext());
        etSearch.setHint("Search memes, users...");
        etSearch.setPadding(32, 16, 32, 16);
        etSearch.setBackgroundResource(R.drawable.edittext_bg);

        TextView tvResult = new TextView(getContext());
        tvResult.setText("Search results will appear here\n\nUse the search bar above");
        tvResult.setTextAlignment(View.TEXT_ALIGNMENT_CENTER);
        tvResult.setPadding(0, 32, 0, 0);

        layout.addView(tvTitle);
        layout.addView(etSearch);
        layout.addView(tvResult);

        return layout;
    }
}